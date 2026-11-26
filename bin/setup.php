<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
if(PHP_SAPI!=='cli')exit;
try {
    $db=Database::connection();
    foreach(explode(';',file_get_contents(BASE.'/database/schema.sql')) as $sql)if(trim($sql)!=='')$db->exec($sql);
    echo "Estrutura criada ou já existente.\n";
    if(in_array('--demo',$argv,true)) {
        if(!extension_loaded('gd'))throw new RuntimeException('Ative GD antes de instalar os dados demonstrativos: php -d extension=gd bin/setup.php --demo');
        if(Database::one('SELECT COUNT(*) n FROM usuarios')['n']>0)throw new RuntimeException('Demonstração só pode ser criada em banco vazio.');
        $pw=env('DEMO_ADMIN_PASSWORD');$epw=env('DEMO_EDITOR_PASSWORD');
        if(strlen($pw)<12 || strlen($epw)<12 || strlen($pw)>72 || strlen($epw)>72 || $pw===$epw)throw new RuntimeException('Defina DEMO_ADMIN_PASSWORD e DEMO_EDITOR_PASSWORD distintos (12 a 72 caracteres).');
        $db->beginTransaction();
        try {
            Database::run('INSERT INTO usuarios (nome,email,senha_hash,perfil) VALUES (?,?,?,?),(?,?,?,?)',['Administração Demo','admin@vitrine.example',password_hash($pw,PASSWORD_DEFAULT),'administrador','Edição Demo','editor@vitrine.example',password_hash($epw,PASSWORD_DEFAULT),'editor']);
            foreach(explode(';',file_get_contents(BASE.'/database/seed.sql')) as $sql)if(trim($sql)!=='')$db->exec($sql);
            $db->commit();
        }catch(Throwable $ex){$db->rollBack();throw $ex;}
        $titles=[
          ['Conecta Campus: oportunidades que aproximam',1,1,1,1,'Uma plataforma para conectar estudantes a projetos de extensão e oportunidades de aprendizagem. O protótipo reúne inscrições, acompanhamento e comunicação acessível. A avaliação demonstrativa utiliza cenários simulados e não representa resultados de pesquisa com pessoas reais.'],
          ['EcoPonto: pequenas ações, grandes mudanças',2,4,2,3,'Uma proposta de gestão de pontos de coleta para estimular a destinação correta de resíduos. O projeto mapeia processos, responsabilidades e indicadores de acompanhamento, com dados inteiramente fictícios.'],
          ['Mente em Dia: bem-estar na rotina acadêmica',3,5,3,4,'Uma iniciativa educativa para organizar ações de bem-estar na comunidade acadêmica. O material apresenta planejamento de oficinas e recursos informativos, sem realizar diagnósticos ou substituir atendimento profissional.'],
          ['Luz Inteligente: energia no momento certo',1,2,1,2,'Um sistema demonstrativo com sensores para monitorar luminosidade e presença. A proposta integra um painel de indicadores e uma estratégia de acionamento eficiente em ambientes de estudo.'],
          ['Bairro Empreende: negócios em rede',2,3,2,3,'Um catálogo de negócios locais com metodologia de organização de informações e divulgação responsável. O estudo usa estabelecimentos fictícios para explorar estratégias de colaboração e planejamento.'],
          ['Rotas Acessíveis: a cidade para todos',1,1,1,1,'Uma aplicação que organiza observações de acessibilidade em trajetos urbanos. O protótipo foi construído com rotas fictícias para discutir navegação inclusiva e apresentação clara de informações.'],
          ['Pausa Ativa: cuidado no trabalho',3,5,3,4,'Um plano de ações educativas para incentivar pausas e hábitos de autocuidado. As atividades e os indicadores apresentados são demonstrativos e dependem de avaliação institucional para aplicação real.'],
          ['Observatório de Dados da Comunidade',1,1,1,2,'Um painel demonstrativo que organiza dados públicos fictícios em indicadores compreensíveis. A proposta valoriza a transparência e o aprendizado de visualização de dados.'],
          ['Feira Circular: consumo com propósito',2,4,2,3,'Uma solução de organização de feiras de troca e reaproveitamento de materiais. O projeto integra planejamento operacional, comunicação e indicadores simulados de participação.'],
          ['Biblioteca Viva: histórias que circulam',1,1,1,1,'Um catálogo acessível de materiais educacionais com busca e organização por temas. A proposta explora componentes reutilizáveis e métodos de avaliação de usabilidade.'],
          ['Projeto em desenvolvimento: laboratório aberto',1,1,1,2,'Registro demonstrativo em rascunho para validar o controle de publicação e o fluxo de revisão da equipe.'],
          ['Memória de uma pesquisa: protótipo arquivado',2,3,2,3,'Registro demonstrativo arquivado para validar a preservação de conteúdo fora da consulta pública.']
        ];
        foreach($titles as $n=>[$title,$area,$sub,$course,$uc,$summary]) {
            ProjectService::save(['titulo'=>$title,'resumo'=>$summary,'objetivos'=>"Desenvolver uma solução aplicada ao contexto acadêmico.\nDocumentar decisões, avaliar a proposta com cenários simulados e compartilhar o aprendizado com a comunidade.",'edicao_id'=>$n<7?2:1,'area_id'=>$area,'subarea_id'=>$sub,'instituicao_id'=>1,'curso_id'=>$course,'unidade_curricular_id'=>$uc,'responsavel_id'=>$area===3?3:1,'situacao'=>$n===10?'rascunho':($n===11?'arquivado':'publicado'),'destaque'=>$n<3?1:0,'autores'=>[$n%5+1,($n+1)%5+1],'colaboradores'=>[2],'palavras'=>[$area===1?4:($area===2?2:6),1]],['id'=>1,'perfil'=>'administrador']);
        }
        // Produz materiais locais autossuficientes: imagens raster e PDF demonstrativo.
        require BASE.'/bin/demo-materials.php';
        echo "Demonstração criada: 12 projetos, 10 publicados, 2 edições.\nUsuários: admin@vitrine.example e editor@vitrine.example. Use as senhas fornecidas no ambiente.\n";
    }
}catch(Throwable $ex){fwrite(STDERR,'Falha na instalação: '.$ex->getMessage()."\n");exit(1);}
