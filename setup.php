<?php
// يُنشئ الجداول والمحتوى الافتراضي وحساب المدير. يرفض العمل بعد إنشاء أول مدير. احذفه بعد الاستخدام.
require __DIR__.'/api/config.php';header('Content-Type: text/html; charset=utf-8');$db=db();
foreach(['CREATE TABLE IF NOT EXISTS admin_users(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(50) UNIQUE NOT NULL,pass_hash VARCHAR(255) NOT NULL)',
'CREATE TABLE IF NOT EXISTS site_content(k VARCHAR(50) PRIMARY KEY,v TEXT)',
'CREATE TABLE IF NOT EXISTS services(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,description TEXT,sort_order INT DEFAULT 0)',
'CREATE TABLE IF NOT EXISTS categories(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL)',
'CREATE TABLE IF NOT EXISTS works(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,description TEXT,category_id INT NULL,image VARCHAR(100),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)',
'CREATE TABLE IF NOT EXISTS partners(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,kind VARCHAR(10) DEFAULT "partner",logo VARCHAR(100))'] as $q)$db->exec($q);
$s=$db->prepare('INSERT IGNORE INTO site_content(k,v) VALUES(?,?)');
foreach(['hero_title'=>'حلول الأمن والسلامة المتكاملة','hero_text'=>'تصميم وتركيب وصيانة أنظمة كاميرات المراقبة وإنذار الحريق والسرقة والإطفاء الآلي والشبكات الحاسوبية وشبكات WiFi.','about_text'=>"المهندس محمد عبد الرزاق قاسم، مختص بأنظمة المراقبة والإنذار والأمن.\nنقدّم في Elite Group حلولاً متكاملة من الدراسة والتصميم حتى التركيب والصيانة، بخبرة عملية في كاميرات المراقبة، إنذار الحريق، الإطفاء الآلي FM200، كشف التسلل، التحكم بالدخول، الحضور والانصراف، والشبكات السلكية واللاسلكية.",'phone1'=>'','phone2'=>'','whatsapp'=>'','email'=>'','address'=>'ريف دمشق، سوريا','hours'=>'','facebook'=>'','instagram'=>''] as $k=>$v)$s->execute([$k,$v]);
if(!$db->query('SELECT COUNT(*) FROM services')->fetchColumn()){$s=$db->prepare('INSERT INTO services(title,description,sort_order) VALUES(?,?,?)');
foreach([['كاميرات المراقبة CCTV','تصميم وتركيب أنظمة مراقبة IP وتناظرية مع التسجيل والمشاهدة عن بُعد.'],['إنذار الحريق','أنظمة كشف وإنذار حريق عنونة وتقليدية وفق المعايير.'],['الإنذار ضد السرقة','أنظمة كشف التسلل وحساسات الحركة وملحقات الإنذار.'],['الإطفاء الآلي FM200','أنظمة إطفاء بالغاز النظيف لغرف السيرفرات والمعدات الحساسة.'],['التحكم بالدخول والحضور','أقفال وقارئات بطاقات وبصمة وأنظمة حضور وانصراف.'],['الشبكات وWiFi','شبكات سلكية ولاسلكية وتغطية WiFi للمنشآت.']] as $i=>$r)$s->execute([$r[0],$r[1],$i]);}
if($db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn())exit('<p dir=rtl>تم الإعداد مسبقاً. احذف الملف setup.php من الخادم.</p>');
$m='';if($_SERVER['REQUEST_METHOD']==='POST'){
 if(strlen($_POST['p']??'')<8||!trim($_POST['u']??''))$m='كلمة المرور 8 أحرف على الأقل واسم المستخدم مطلوب.';
 else{$db->prepare('INSERT INTO admin_users(username,pass_hash) VALUES(?,?)')->execute([trim($_POST['u']),password_hash($_POST['p'],PASSWORD_DEFAULT)]);exit('<p dir=rtl>تم إنشاء الحساب. <b>احذف setup.php الآن</b> ثم ادخل إلى <a href="admin.html">admin.html</a></p>');}}
?><!doctype html><meta charset=utf-8><meta name=viewport content="width=device-width"><body dir=rtl style="font:16px Tahoma;max-width:360px;margin:60px auto">
<h3>إنشاء حساب المدير</h3><p style="color:#c00"><?=htmlspecialchars($m)?></p>
<form method=post><p><input name=u placeholder="اسم المستخدم" required style="width:100%;padding:8px"></p><p><input name=p type=password placeholder="كلمة المرور (8+)" required style="width:100%;padding:8px"></p><button style="padding:8px 20px">إنشاء</button></form>
