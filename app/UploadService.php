<?php
final class UploadService {
    public static function store(array $file, int $project, string $alt, array $user): int {
        self::authorize($user);
        ProjectRepository::detail($project,false);
        if (($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK) throw new InvalidArgumentException('Upload não concluído. Confira o limite do PHP e tente novamente.');
        $max=min(20,max(1,(int)env('UPLOAD_MAX_MB','10')))*1024*1024;
        if ($file['size']>$max || $file['size']<1 || !is_uploaded_file($file['tmp_name'])) throw new InvalidArgumentException('Arquivo inválido ou acima do limite.');
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extensions=['image/jpeg'=>['jpg','jpeg'],'image/png'=>['png'],'image/webp'=>['webp'],'application/pdf'=>['pdf']];
        $extension=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
        if (!isset($extensions[$mime]) || !in_array($extension,$extensions[$mime],true)) throw new InvalidArgumentException('Envie JPEG, PNG, WebP ou PDF com extensão e conteúdo compatíveis.');
        $alt=trim($alt); if ($alt==='' || mb_strlen($alt)>200) throw new InvalidArgumentException('Informe uma descrição acessível de até 200 caracteres.');
        if (str_starts_with($mime,'image/')) {
            if (!extension_loaded('gd')) throw new InvalidArgumentException('A extensão GD precisa estar habilitada no servidor para receber imagens.');
            $size=getimagesize($file['tmp_name']);
            if (!$size || $size[0]*$size[1]>24000000) throw new InvalidArgumentException('Imagem inválida ou maior que 24 megapixels.');
        } else {
            $bytes=file_get_contents($file['tmp_name']);
            if (!str_starts_with($bytes,'%PDF-') || preg_match('~/JavaScript|/JS\s|/Launch|/EmbeddedFile|/OpenAction|/AA\s~i',$bytes)) throw new InvalidArgumentException('PDF inválido ou com recursos ativos não permitidos.');
        }
        $name=bin2hex(random_bytes(24)).'.'.$extensions[$mime][0]; $path=BASE.'/storage/uploads/'.$name;
        if (str_starts_with($mime,'image/')) {
            $image=imagecreatefromstring(file_get_contents($file['tmp_name']));
            if (!$image) throw new InvalidArgumentException('Não foi possível processar a imagem.');
            $w=imagesx($image);$h=imagesy($image);$ratio=min(1,1920/max($w,$h));
            $out=imagecreatetruecolor(max(1,(int)($w*$ratio)),max(1,(int)($h*$ratio)));
            $white=imagecolorallocate($out,255,255,255);imagefill($out,0,0,$white);
            imagecopyresampled($out,$image,0,0,0,0,imagesx($out),imagesy($out),$w,$h);
            $written=imagejpeg($out,$path,85);imagedestroy($out);imagedestroy($image);
            if(!$written)throw new RuntimeException('Falha ao armazenar a imagem otimizada.');
            $newName=pathinfo($name,PATHINFO_FILENAME).'.jpg';
            if($name!==$newName && !rename($path,BASE.'/storage/uploads/'.$newName)) {unlink($path);throw new RuntimeException('Falha ao finalizar a imagem otimizada.');}
            $name=$newName;$path=BASE.'/storage/uploads/'.$name;$mime='image/jpeg';
        } elseif (!move_uploaded_file($file['tmp_name'],$path)) throw new RuntimeException('Falha ao armazenar arquivo.');
        try {
            Database::run('INSERT INTO arquivos (trabalho_id,nome_original,nome_storage,mime,tamanho,texto_alternativo) VALUES (?,?,?,?,?,?)',[$project,mb_substr(basename($file['name']),0,200),$name,$mime,filesize($path),$alt]);
            $id=(int)Database::connection()->lastInsertId(); ProjectService::audit('upload','arquivos',$id,(int)$user['id']);return $id;
        } catch(Throwable $ex) { unlink($path); throw $ex; }
    }
    public static function link(array $in, int $project, array $user, ?int $id = null): void {
        self::authorize($user);
        ProjectRepository::detail($project,false);
        $url=trim((string)($in['url']??'')); $title=trim((string)($in['titulo']??'')); $type=$in['tipo']??'';
        if (!filter_var($url,FILTER_VALIDATE_URL) || !in_array(strtolower(parse_url($url,PHP_URL_SCHEME)??''),['https','http'],true) || parse_url($url,PHP_URL_USER)!==null || strlen($url)>2000) throw new InvalidArgumentException('Informe uma URL HTTP ou HTTPS válida, sem credenciais.');
        if ($title==='' || mb_strlen($title)>150 || !in_array($type,['video','externo'],true)) throw new InvalidArgumentException('Título ou tipo de link inválido.');
        if($id) {
            if(!Database::one('SELECT id FROM links WHERE id=? AND trabalho_id=?',[$id,$project]))throw new HttpError(404,'Link não encontrado.');
            Database::run('UPDATE links SET titulo=?,url=?,tipo=? WHERE id=? AND trabalho_id=?',[$title,$url,$type,$id,$project]);
        }else {Database::run('INSERT INTO links (trabalho_id,titulo,url,tipo) VALUES (?,?,?,?)',[$project,$title,$url,$type]);$id=(int)Database::connection()->lastInsertId();}
        ProjectService::audit('salvar','links',$id,(int)$user['id']);
    }
    private static function authorize(array $user): void {
        if (!in_array($user['perfil'] ?? '', ['administrador','editor'], true)) throw new HttpError(403,'Perfil sem permissão para gerenciar materiais.');
    }
    public static function embedUrl(string $url): ?string {
        $host=strtolower(parse_url($url,PHP_URL_HOST)??'');
        $path=parse_url($url,PHP_URL_PATH)??'';
        $id='';
        if(in_array($host,['youtube.com','www.youtube.com','m.youtube.com'],true)) {
            parse_str(parse_url($url,PHP_URL_QUERY)??'',$query);$id=$query['v']??'';
            if(preg_match('~^/(?:embed|shorts)/([a-zA-Z0-9_-]{11})$~',$path,$m))$id=$m[1];
        }elseif($host==='youtu.be')$id=ltrim($path,'/');
        elseif(in_array($host,['vimeo.com','www.vimeo.com'],true) && preg_match('~^/(\d+)$~',$path,$m))return 'https://player.vimeo.com/video/'.$m[1];
        return is_string($id) && preg_match('/^[a-zA-Z0-9_-]{11}$/D',$id)?'https://www.youtube-nocookie.com/embed/'.$id:null;
    }
}
