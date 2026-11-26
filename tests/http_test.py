"""Testes reais HTTP. Nome diferente de http.py para não sombrear a biblioteca padrão."""
import http.cookiejar
import json
import os
import re
import sys
import struct
import time
import urllib.error
import urllib.parse
import urllib.request
import zlib

BASE = os.getenv('TEST_URL', 'http://127.0.0.1:8080').rstrip('/')
if not os.getenv('DB_DATABASE', '').endswith('_test'):
    sys.exit('Recusado: defina DB_DATABASE com sufixo _test; não execute em dados reais.')
results = []
jar = http.cookiejar.CookieJar()
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))

def request(path, data=None, headers=None):
    body = urllib.parse.urlencode(data, doseq=True).encode() if isinstance(data, dict) else data
    encoded_path = urllib.parse.quote(path.lstrip('/'), safe='/?=&%+:#')
    req = urllib.request.Request(BASE + '/' + encoded_path, data=body, headers=headers or {})
    try:
        with client.open(req, timeout=10) as r:
            return r.status, r.read(), r.headers, r.url
    except urllib.error.HTTPError as e:
        return e.code, e.read(), e.headers, e.url

def html(path):
    return request(path)[1].decode('utf-8')

def csrf(page):
    return re.search(r'name="csrf" value="([a-f0-9]+)"', page).group(1)

def test(name, fn):
    try:
        assert fn(), 'Resultado diferente do esperado'
        results.append(dict(teste=name, status='aprovado'))
    except Exception as e:
        results.append(dict(teste=name, status='reprovado', erro=str(e)))

try:
    status, _, _, _ = request('login')
except Exception as e:
    print(json.dumps(dict(suite='http', status='bloqueado', motivo=str(e)), ensure_ascii=False, indent=2))
    sys.exit(2)

test('CA01 página institucional', lambda: 'Faculdade Senac Joinville' in html(''))
test('CA02 vitrine contém publicados', lambda: 'Conecta Campus' in html('vitrine'))
test('CA03 filtro de área', lambda: 'Conecta Campus' in html('vitrine?area=1') and 'Mente em Dia' not in html('vitrine?area=1'))
test('CA04 busca por título', lambda: '1 projeto encontrado' in html('vitrine?q=Conecta+Campus'))
test('CA05 busca por autor', lambda: 'Conecta Campus' in html('vitrine?q=Clara+Martins'))
test('Busca por dois autores', lambda: 'Conecta Campus' in html('vitrine?q=Clara+Lucas'))
test('Combinação área e edição', lambda: 'Edição selecionada' in html('vitrine?area=1&edicao=2'))
test('Mensagem sem resultados', lambda: 'Nenhum projeto' in html('vitrine?q=zzzinexistentezzz'))
test('Rascunho não aparece na busca', lambda: '0 projetos encontrados' in html('vitrine?q=laboratório+aberto'))
test('CA06 detalhes completos e PDF', lambda: all(x in html('projeto/1') for x in ['Conecta Campus', 'Clara Martins', 'Helena Duarte', 'Resumo', 'Unidade curricular', 'Documentos do projeto', 'Baixar']))
test('Arquivo de projeto publicado acessível', lambda: request('arquivo/1')[0] == 200)
test('Projeto rascunho por ID bloqueado', lambda: request('projeto/11')[0] == 404)
test('Projeto arquivado por ID bloqueado', lambda: request('projeto/12')[0] == 404)
test('CA07 painel anônimo redireciona ao login', lambda: request('admin')[3].endswith('/login'))
test('CSRF ausente retorna 419', lambda: request('login', {'email':'admin@vitrine.example','senha':'x'})[0] == 419)
test('CSRF forjado retorna 419', lambda: request('login', {'csrf':'x','email':'admin@vitrine.example','senha':'x'})[0] == 419)
test('Tentativa de SQLi não autentica', lambda: request('login', {'csrf':csrf(html('login')),'email':"' OR 1=1 --",'senha':'x'})[0] == 422)
test('Busca XSS é escapada', lambda: '<script>alert(1)</script>' not in html('vitrine?q=%3Cscript%3Ealert(1)%3C%2Fscript%3E'))
test('CSP e nosniff enviados', lambda: request('login')[2].get('X-Content-Type-Options') == 'nosniff' and "script-src 'self'" in request('login')[2].get('Content-Security-Policy',''))
test('Sessão HttpOnly e SameSite', lambda: any(c.has_nonstandard_attr('HttpOnly') and c.get_nonstandard_attr('SameSite','').lower()=='lax' for c in jar))
test('Credenciais e código não são servidos', lambda: request('.env')[0] == 404 and request('app/Database.php')[0] == 404)
test('Catálogo de impressão', lambda: 'Imprimir ou salvar em PDF' in html('catalogo'))

def login(email, password):
    return request('login', dict(csrf=csrf(html('login')),email=email,senha=password))[3].endswith('/admin')

test('Login administrador', lambda: login('admin@vitrine.example', os.getenv('DEMO_ADMIN_PASSWORD','')))
test('Dashboard e navegação administrativa', lambda: all(s in html('admin') for s in ['Visão geral','Cadastros','Sair']))

def create_edition():
    page=html('admin/cadastros/edicoes/novo')
    code, content, _, _=request('admin/cadastros/edicoes/novo',dict(csrf=csrf(page),nome='HTTP Teste '+str(time.time_ns()),ano=2027,descricao='Teste HTTP'))
    return code==200 and 'Cadastro atualizado' in content.decode('utf-8')
test('CA11 cadastro de edição pelo formulário', create_edition)

project_data=dict(titulo='Teste HTTP '+str(time.time_ns()),resumo='Resumo HTTP',objetivos='Objetivos HTTP',edicao_id=2,area_id=1,subarea_id=1,instituicao_id=1,curso_id=1,unidade_curricular_id=1,responsavel_id=1,situacao='publicado',autores=[1,2],colaboradores=[2],palavras=[1])
project_id=None

def create_project():
    global project_id
    data=project_data.copy()
    data['autores[]']=data.pop('autores');data['colaboradores[]']=data.pop('colaboradores');data['palavras[]']=data.pop('palavras')
    data['csrf']=csrf(html('admin/projetos/novo'))
    status, body, _, url=request('admin/projetos/novo',data)
    match=re.search(r'/admin/projetos/(\d+)$',url)
    if match: project_id=int(match.group(1))
    return status==200 and match is not None and project_data['titulo'] in body.decode('utf-8')
test('CA08 cadastro de projeto por HTTP', create_project)

def multipart(name, mime, content, alt='Imagem de teste'):
    boundary='VitrineBoundary'+str(time.time_ns())
    token=csrf(html(f'admin/projetos/{project_id}'))
    parts=[]
    for key,val in [('csrf',token),('texto_alternativo',alt)]:
        parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="{key}"\r\n\r\n{val}\r\n'.encode())
    parts.append(f'--{boundary}\r\nContent-Disposition: form-data; name="arquivo"; filename="{name}"\r\nContent-Type: {mime}\r\n\r\n'.encode()+content+b'\r\n')
    parts.append(f'--{boundary}--\r\n'.encode())
    return request(f'admin/projetos/{project_id}/upload', b''.join(parts), {'Content-Type':f'multipart/form-data; boundary={boundary}'})[1].decode('utf-8')

if project_id:
    def chunk(kind, data):
        return struct.pack('!I',len(data))+kind+data+struct.pack('!I',zlib.crc32(kind+data)&0xffffffff)
    png=b'\x89PNG\r\n\x1a\n'+chunk(b'IHDR',struct.pack('!IIBBBBB',1,1,8,2,0,0,0))+chunk(b'IDAT',zlib.compress(b'\x00\xff\x80\x00'))+chunk(b'IEND',b'')
    test('Upload PNG válido é otimizado', lambda: 'image/jpeg' in multipart('imagem.png','image/png',png))
    test('Upload PHP é recusado', lambda: 'conteúdo compatíveis' in multipart('shell.php','application/x-php',b'<?php echo 1;'))
    test('Extensão e MIME incompatíveis recusados', lambda: 'conteúdo compatíveis' in multipart('imagem.jpg','image/jpeg',b'%PDF-1.4\n'))
    test('PDF ativo é recusado', lambda: 'recursos ativos' in multipart('ativo.pdf','application/pdf',b'%PDF-1.4\n/JavaScript /OpenAction'))
    test('Imagem sem descrição recusada', lambda: 'descrição acessível' in multipart('imagem.png','image/png',png,alt=''))
    pdf_id=int(re.search(r'<iframe[^>]+src="[^\"]*/arquivo/(\d+)"',html('projeto/1')).group(1))
    pdf_bytes=request(f'arquivo/{pdf_id}')[1]
    test('PDF demonstrativo é aceito', lambda: 'application/pdf' in multipart('demo.pdf','application/pdf',pdf_bytes,alt='Documento de teste'))
    test('Material de outro projeto não pode ser removido pelo ID',lambda:request(f'admin/projetos/{project_id}/remover-arquivo',{'csrf':csrf(html(f'admin/projetos/{project_id}')),'material_id':1})[0]==404 and request('arquivo/1')[0]==200)

    def edit_to_draft():
        page=html(f'admin/projetos/{project_id}')
        version=re.search(r'name="versao" value="(\d+)"',page).group(1)
        data=project_data.copy()
        data.update(titulo='HTTP atualizado',situacao='rascunho',versao=version,csrf=csrf(page))
        data['autores[]']=data.pop('autores');data['colaboradores[]']=data.pop('colaboradores');data['palavras[]']=data.pop('palavras')
        status, body, _, _=request(f'admin/projetos/{project_id}',data)
        return status==200 and 'HTTP atualizado' in body.decode('utf-8')
    test('CA09 edição por HTTP',edit_to_draft)
    test('CA10 publicação removida após rascunho',lambda:request(f'projeto/{project_id}')[0]==404)
    request(f'admin/projetos/{project_id}/excluir',{'csrf':csrf(html(f'admin/projetos/{project_id}'))})

request('logout',{'csrf':csrf(html('admin'))})
test('Login editor',lambda:login('editor@vitrine.example',os.getenv('DEMO_EDITOR_PASSWORD','')))
test('Editor não gerencia usuários por URL',lambda:request('admin/cadastros/usuarios')[0]==403)
test('Editor não consulta auditoria',lambda:request('admin/auditoria')[0]==403)
test('Editor não exclui projeto por POST direto',lambda:request('admin/projetos/1/excluir',{'csrf':csrf(html('admin'))})[0]==403)
test('Logout encerra acesso',lambda:request('logout',{'csrf':csrf(html('admin'))})[3].endswith('/') and request('admin')[3].endswith('/login'))
failed=sum(r['status']=='reprovado' for r in results)
print(json.dumps(dict(suite='http',aprovados=len(results)-failed,reprovados=failed,resultados=results),ensure_ascii=False,indent=2))
sys.exit(bool(failed))
