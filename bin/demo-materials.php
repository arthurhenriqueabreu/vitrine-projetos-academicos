<?php
// Chamado exclusivamente pelo setup CLI. Imagens abstratas originais e PDF de demonstração.
if(PHP_SAPI!=='cli')exit;
if(!extension_loaded('gd'))throw new RuntimeException('Ative a extensão GD para gerar e otimizar imagens.');
for($id=1;$id<=10;$id++) {
    $im=imagecreatetruecolor(1000,600);
    $background=imagecolorallocate($im,8+($id%3)*12,48+($id%3)*14,74+($id%3)*12);imagefill($im,0,0,$background);
    $line=imagecolorallocate($im,35,97,126);$light=imagecolorallocate($im,154,205,221);$orange=imagecolorallocate($im,247,149,38);$white=imagecolorallocate($im,235,245,249);
    for($x=-200;$x<1200;$x+=85)imageline($im,$x,0,$x+350,600,$line);
    imageellipse($im,620,300,480,480,$light);imageellipse($im,620,300,350,350,$line);
    imagefilledrectangle($im,430,170,810,425,$white);imagefilledrectangle($im,447,188,793,380,$background);
    imagefilledrectangle($im,560,426,680,443,$orange);imagefilledrectangle($im,520,444,720,454,$light);
    for($x=475;$x<=745;$x+=45) {imagefilledrectangle($im,$x,225,$x+25,345-($x%4)*20,$orange);}
    imagefilledellipse($im,810,170,90,90,$orange);imagestring($im,5,68,75,'SENAC / PROJETOS ACADEMICOS',$white);imagestring($im,5,68,490,'CONHECIMENTO EM MOVIMENTO',$white);
    $name=bin2hex(random_bytes(24)).'.jpg';$file=BASE.'/storage/uploads/'.$name;imagejpeg($im,$file,88);imagedestroy($im);
    Database::run('INSERT INTO arquivos (trabalho_id,nome_original,nome_storage,mime,tamanho,texto_alternativo) VALUES (?,?,?,?,?,?)',[$id,'apresentacao-demo.jpg',$name,'image/jpeg',filesize($file),'Ilustração demonstrativa de tecnologia e conexões acadêmicas']);
}
$stream="BT /F1 22 Tf 60 760 Td (Vitrine Digital de Projetos Academicos) Tj 0 -40 Td /F1 12 Tf (Material demonstrativo - dados ficticios) Tj 0 -28 Td (Faculdade Senac Joinville) Tj 0 -28 Td (Objetivo: compartilhar conhecimento e projetos da comunidade.) Tj 0 -28 Td (Este arquivo demonstra visualizacao e download de PDF.) Tj ET";
$objects=['<< /Type /Catalog /Pages 2 0 R >>','<< /Type /Pages /Kids [3 0 R] /Count 1 >>','<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>','<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>','<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream"];
$pdf="%PDF-1.4\n";$offsets=[0];foreach($objects as $n=>$obj){$offsets[]=strlen($pdf);$pdf.=($n+1)." 0 obj\n$obj\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 6\n0000000000 65535 f \n";for($i=1;$i<=5;$i++)$pdf.=sprintf('%010d 00000 n ',$offsets[$i])."\n";$pdf.="trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
$name=bin2hex(random_bytes(24)).'.pdf';file_put_contents(BASE.'/storage/uploads/'.$name,$pdf);
Database::run('INSERT INTO arquivos (trabalho_id,nome_original,nome_storage,mime,tamanho,texto_alternativo) VALUES (?,?,?,?,?,?)',[1,'resumo-demonstrativo.pdf',$name,'application/pdf',strlen($pdf),'Resumo demonstrativo da proposta']);
Database::run('INSERT INTO links (trabalho_id,titulo,url,tipo) VALUES (?,?,?,?),(?,?,?,?)',[1,'Portal institucional do Senac SC','https://www.sc.senac.br/','externo',1,'Canal institucional do Senac SC (referência de comunicação, não é vídeo deste projeto fictício)','https://www.youtube.com/@SenacSC','video']);
