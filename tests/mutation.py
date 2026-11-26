"""Confere que remover cada guarda crítica faz o cenário específico falhar.

Cópias descartáveis ficam em runtime/mutations. Nunca modifica o código da aplicação.
"""
import json
import os
from pathlib import Path
import shutil
import subprocess
import sys
import tempfile

root=Path(__file__).resolve().parents[1]
php=os.getenv('PHP_BIN',r'C:\xampp\php\php.exe' if os.name=='nt' else 'php')
mutations=[
 ('titulo','app/ProjectService.php',"$v==='' || mb_strlen($v)>$max","($k!=='titulo' && $v==='') || mb_strlen($v)>$max",'Título obrigatório'),
 ('curso','app/ProjectService.php',"if ($data[$k]<1)","if ($k!=='curso_id' && $data[$k]<1)",'Associação obrigatória ao curso'),
 ('situacao','app/ProjectService.php',"if (!in_array($data['situacao'],['rascunho','publicado','arquivado'],true))","if (false)",'Situação fora da lista é recusada'),
 ('responsavel','app/ProjectService.php',"if (in_array($data['responsavel_id'],$collabs,true))","if (false)",'Responsável não pode colaborar no mesmo trabalho'),
 ('limite_ids','app/ProjectService.php',"count($ids)>$max","false",'Máximo de cinco autores'),
 ('editor_excluir','app/ProjectService.php',"if ($user['perfil']!=='administrador')","if (false)",'Editor não exclui projetos'),
 ('csrf','app/bootstrap.php',"if (!hash_equals(csrf(), (string) ($_POST['csrf'] ?? '')))","if (false)",'CSRF ausente é recusado'),
]
results=[]
(root/'runtime/mutations').mkdir(parents=True,exist_ok=True)
for name,file,old,new,target in mutations:
    if target is None:continue
    with tempfile.TemporaryDirectory(prefix=name+'-',dir=root/'runtime/mutations') as tmp:
        task=Path(tmp)
        for directory in ['app','views','tests']:
            shutil.copytree(root/directory,task/directory,ignore=shutil.ignore_patterns('__pycache__'))
        (task/'storage/logs').mkdir(parents=True)
        path=task/file;text=path.read_text(encoding='utf-8')
        if old not in text:raise RuntimeError('Guarda não encontrada: '+name)
        path.write_text(text.replace(old,new,1),encoding='utf-8')
        run=subprocess.run([php,str(task/'tests/unit.php')],capture_output=True,timeout=15)
        try:
            suite=json.loads(run.stdout.decode('utf-8'))
            intended=next(r for r in suite['resultados'] if r['teste']==target)
            killed=run.returncode!=0 and intended['status']=='reprovado'
            results.append(dict(mutacao=name,cenario=target,status='detectada' if killed else 'nao_detectada'))
        except Exception as e:
            results.append(dict(mutacao=name,cenario=target,status='erro_execucao',erro=str(e)))
out=dict(suite='mutation',detectadas=sum(r['status']=='detectada' for r in results),total=len(results),resultados=results)
print(json.dumps(out,ensure_ascii=False,indent=2))
sys.exit(any(r['status']!='detectada' for r in results))
