"""Diagnóstico somente leitura de rede. Não altera firewall, ACL, UAC ou antivírus."""
import datetime
import json
import os
from pathlib import Path
import socket
import subprocess

results={"data":datetime.datetime.now(datetime.timezone.utc).isoformat(),"sistema":os.name,"conexoes":[]}
for port in [3306,3308,8080,49153]:
    with socket.socket(socket.AF_INET,socket.SOCK_STREAM) as client:
        client.settimeout(2)
        try:
            client.connect(('127.0.0.1',port));outcome={'porta':port,'resultado':'conectado'}
        except OSError as e:
            outcome={'porta':port,'resultado':'erro','errno':e.errno,'winerror':getattr(e,'winerror',None),'mensagem':str(e)}
        results['conexoes'].append(outcome)
with socket.socket(socket.AF_INET,socket.SOCK_STREAM) as server:
    try:
        server.bind(('127.0.0.1',0));server.listen(1);results['bind_efemero']='aprovado'
    except OSError as e:
        results['bind_efemero']={'errno':e.errno,'winerror':getattr(e,'winerror',None),'mensagem':str(e)}
if os.name=='nt':
    token=subprocess.run(['whoami','/groups'],capture_output=True)
    results['token_baixa_integridade']='S-1-16-4096' in token.stdout.decode(errors='replace')
    try:
        docker=subprocess.run(['docker','version'],capture_output=True,timeout=10)
        results['docker']={'exit_code':docker.returncode,'saida':(docker.stdout+docker.stderr).decode('utf-8',errors='replace')}
    except (OSError,subprocess.TimeoutExpired) as e:results['docker']={'erro':str(e)}
results['conclusao']='Falhas 10013 na conexão, mesmo em portas distintas, são compatíveis com restrição do contexto de execução. Não foi identificada com certeza a regra específica de Windows Filtering Platform/firewall. Não alterar segurança: usar CI com MySQL real ou terminal normal autorizado.'
target=Path(__file__).resolve().parents[1]/'docs/evidencias/rede-windows.json'
target.write_text(json.dumps(results,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(results,ensure_ascii=False,indent=2))
