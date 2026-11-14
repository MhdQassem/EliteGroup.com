<?php
require __DIR__.'/config.php';
session_set_cookie_params(['httponly'=>true,'samesite'=>'Strict','secure'=>!empty($_SERVER['HTTPS'])]);
session_start();
header('Content-Type: application/json; charset=utf-8');
function out($d,$c=200){http_response_code($c);echo json_encode($d,JSON_UNESCAPED_UNICODE);exit;}
$db=db();$a=$_GET['a']??'';$in=json_decode(file_get_contents('php://input'),true)??[];
// الأعمدة المسموح بتعديلها لكل جدول + عمود الصورة (قائمة بيضاء ضد حقن SQL)
$T=['services'=>['title','description','sort_order'],'categories'=>['name'],'works'=>['title','description','category_id','image'],'partners'=>['name','kind','logo']];
$F=['works'=>'image','partners'=>'logo'];

if($a==='public'){
  $c=[];foreach($db->query('SELECT k,v FROM site_content') as $r)$c[$r['k']]=$r['v'];
  out(['content'=>$c,
   'services'=>$db->query('SELECT * FROM services ORDER BY sort_order,id')->fetchAll(),
   'categories'=>$db->query('SELECT * FROM categories ORDER BY id')->fetchAll(),
   'works'=>$db->query('SELECT * FROM works ORDER BY id DESC')->fetchAll(),
   'partners'=>$db->query('SELECT * FROM partners ORDER BY id')->fetchAll()]);
}
if($a==='me')out(['user'=>$_SESSION['u']??null,'csrf'=>$_SESSION['csrf']??null]);
if($_SERVER['REQUEST_METHOD']!=='POST')out(['error'=>'method'],405);

if($a==='login'){
  $s=$db->prepare('SELECT * FROM admin_users WHERE username=?');$s->execute([$in['username']??'']);$u=$s->fetch();
  if(!$u||!password_verify($in['password']??'',$u['pass_hash'])){sleep(1);out(['error'=>'bad_login'],401);}
  session_regenerate_id(true);$_SESSION['u']=$u['id'];$_SESSION['csrf']=bin2hex(random_bytes(16));
  out(['csrf'=>$_SESSION['csrf']]);
}
if(empty($_SESSION['u'])||!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF']??''))out(['error'=>'auth'],403);

function rmimg($t,$id){global $db,$F;if(!isset($F[$t]))return;
  $s=$db->prepare("SELECT {$F[$t]} FROM $t WHERE id=?");$s->execute([$id]);
  if($n=$s->fetchColumn())@unlink(__DIR__.'/../uploads/'.basename($n));}
function saveimg($f){
  if($f['error']||$f['size']>8e6)out(['error'=>'size'],400);
  $x=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name'])]??null;
  if(!$x)out(['error'=>'type'],400);
  $n=bin2hex(random_bytes(8)).".$x";move_uploaded_file($f['tmp_name'],__DIR__."/../uploads/$n");return $n;}

if($a==='logout'){session_destroy();out(['ok'=>1]);}
if($a==='content'){
  $k=$db->query('SELECT k FROM site_content')->fetchAll(PDO::FETCH_COLUMN);
  $s=$db->prepare('UPDATE site_content SET v=? WHERE k=?');
  foreach($in as $x=>$v)if(in_array($x,$k,true))$s->execute([(string)$v,$x]);
  out(['ok'=>1]);
}
if($a==='save'){
  $t=$_POST['t']??'';if(!isset($T[$t]))out(['error'=>'bad_table'],400);
  $id=(int)($_POST['id']??0);$d=[];
  foreach($T[$t] as $c)if(isset($_POST[$c]))$d[$c]=($_POST[$c]===''&&$c==='category_id')?null:$_POST[$c];
  if(isset($F[$t])&&!empty($_FILES['file']['tmp_name'])){if($id)rmimg($t,$id);$d[$F[$t]]=saveimg($_FILES['file']);}
  if(!$d)out(['error'=>'empty'],400);
  if($id){$db->prepare("UPDATE $t SET ".implode(',',array_map(fn($c)=>"$c=?",array_keys($d)))." WHERE id=?")->execute([...array_values($d),$id]);}
  else{$db->prepare("INSERT INTO $t(".implode(',',array_keys($d)).') VALUES('.implode(',',array_fill(0,count($d),'?')).')')->execute(array_values($d));}
  out(['ok'=>1]);
}
if($a==='del'){
  $t=$in['t']??'';$id=(int)($in['id']??0);if(!isset($T[$t]))out(['error'=>'bad_table'],400);
  rmimg($t,$id);$db->prepare("DELETE FROM $t WHERE id=?")->execute([$id]);
  if($t==='categories')$db->prepare('UPDATE works SET category_id=NULL WHERE category_id=?')->execute([$id]);
  out(['ok'=>1]);
}
if($a==='pass'){
  $s=$db->prepare('SELECT pass_hash FROM admin_users WHERE id=?');$s->execute([$_SESSION['u']]);
  if(!password_verify($in['old']??'',$s->fetchColumn())||strlen($in['new']??'')<8)out(['error'=>'bad_pass'],400);
  $db->prepare('UPDATE admin_users SET pass_hash=? WHERE id=?')->execute([password_hash($in['new'],PASSWORD_DEFAULT),$_SESSION['u']]);
  out(['ok'=>1]);
}
out(['error'=>'unknown'],404);
