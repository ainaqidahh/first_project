<?php
session_start();
ini_set('display_errors', 1);

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login(){
		extract($_POST);
			// $qry = $this->db->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where (username = '".$username."' or matric_no = '".$username."' or email = '".$username."') and password = '".md5($password)."' ");

		$qry = $this->db->query("SELECT *,concat(firstname,' ',lastname) as name FROM users WHERE email = '".$email."' AND password = '".md5($password)."' ");

		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}

	function register(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('cpass')) && !is_numeric($k)){
				if($k =='password')
					$v = md5($v);
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}

		$check = $this->db->query("SELECT * FROM users where email ='$email'")->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if($_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}

		$save = $this->db->query("INSERT INTO users set $data");

		if($save){
			return 1;
		}
	}

	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}

	function save_user(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass')) && !is_numeric($k)){
				if($k =='password'){

					if($v != ""){
						$v = md5($v);
						if(empty($data)){
							$data .= " $k='$v' ";
						}else{
							$data .= ", $k='$v' ";
						}
					}
					
				}else{
					if(empty($data)){
						$data .= " $k='$v' ";
					}else{
						$data .= ", $k='$v' ";
					}
				}
			}
		}

		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if($_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");
		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			return 1;
		}
	}
	function update_user(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','cpass','table')) && !is_numeric($k)){
				if($k =='password'){

					if($v != ""){
						$v = md5($v);
						if(empty($data)){
							$data .= " $k='$v' ";
						}else{
							$data .= ", $k='$v' ";
						}
					}
					
				}else{
					if(empty($data)){
						$data .= " $k='$v' ";
					}else{
						$data .= ", $k='$v' ";
					}
				}
			}
		}
		if($_FILES['img']['tmp_name'] != ''){
			$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
			$data .= ", avatar = '$fname' ";

		}
		$check = $this->db->query("SELECT * FROM users where email ='$email' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set $data");
		}else{
			$save = $this->db->query("UPDATE users set $data where id = $id");
		}

		if($save){
			foreach ($_POST as $key => $value) {
				if($key != 'password' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			if($_FILES['img']['tmp_name'] != '')
			$_SESSION['login_avatar'] = $fname;
			return 1;
		}
	}
	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function upload_file(){
		extract($_FILES['file']);
		// var_dump($_FILES);
		if($tmp_name != ''){
				$fname = strtotime(date('y-m-d H:i')).'_'.$name;
				$move = move_uploaded_file($tmp_name,'assets/uploads/'. $fname);
		}
		if(isset($move) && $move){
			return json_encode(array("status"=>1,"fname"=>$fname));
		}
	}
	function upload_file2(){
		extract($_FILES['file']);
		// var_dump($_FILES);
		if($tmp_name != ''){
				$fname = strtotime(date('y-m-d H:i')).'_'.$name;
				$move = move_uploaded_file($tmp_name,'assets/uploads/'. $fname);
		}
		if(isset($move) && $move){
			return json_encode(array("status"=>1,"fname2"=>$fname));
		}
	}
	function remove_file(){
		extract($_POST);
		if(is_file('assets/uploads/'.$fname))
			unlink('assets/uploads/'.$fname);
		return 1;
	}
	function remove_file2(){
		extract($_POST);
		if(is_file('assets/uploads/'.$fname))
			unlink('assets/uploads/'.$fname);
		return 1;
	}
	function delete_file(){
		extract($_POST);
		$doc = $this->db->query("SELECT * FROM documents where id= $id")->fetch_array();
		$delete = $this->db->query("DELETE FROM documents where id = ".$id);
		if($delete){
			foreach(json_decode($doc['file_json']) as $k => $v){
				if(is_file('assets/uploads/'.$v))
				unlink('assets/uploads/'.$v);
			}
			return 1;
		}
	}
	function save_upload(){
		extract($_POST);
		// var_dump($_FILES);
		$data = " title ='$title' ";
 		$data .= ", semester_id ='$semester_id' ";
		$data .= ", club_id ='$club_id' ";
		$data .= ", status ='$status' ";
		$data .= ", program_date ='$program_date' ";
		$data .= ", date_updated ='$date_updated' ";
		$data .= ", penasihat_program ='$penasihat_program' ";
		$data .= ", description ='".htmlentities(str_replace("'","&#x2019;",$description))."' ";
		$data .= ", user_id ='{$_SESSION['login_id']}' ";
		$data .= ", file_json ='".json_encode($fname)."' ";
		if(empty($id)){
			$save = $this->db->query("INSERT INTO documents set $data ");
		}else{
			$save = $this->db->query("UPDATE documents set $data where id = $id");
		}
		if($save){

			// send email permohonan baru
			if($_SESSION['login_type']==2){
				$admin = $this->db->query("SELECT * FROM users where `type`= 1 LIMIT 1")->fetch_array();

				$suject = "Permohonan Kertas Kerja Baru $title - ".$_SESSION['login_firstname']." ".$_SESSION['login_lastname'];
				$msg = "Dimaklumkan bahawa terdapat permohonan baru bagi $title oleh ".$_SESSION['login_firstname']." ".$_SESSION['login_lastname'].".

Sekian, untuk makluman dan perhatian pentadbir sistem

Terima kasih.";

			    $mail = new PHPMailer;
			   	$mail->isSMTP();
			   	$mail->SMTPDebug = 0;
			   	$mail->Host = 'smtp.hostinger.com';
			   	$mail->Port = 587;
			   	$mail->SMTPAuth = true;
			   	$mail->Username = 'internbhepfpa@sippp-bhepfpa.com';
			   	$mail->Password = 'Internbhepfpa456#';
			   	$mail->setFrom('internbhepfpa@sippp-bhepfpa.com', 'SiPPP');
			   	$mail->addReplyTo('internbhepfpa@sippp-bhepfpa.com', 'SiPPP');
			   	$mail->addAddress($admin["email"], $admin["firstname"]." ".$admin["lastname"]);
			   	$mail->Subject = $suject;
			   	$mail->Body = $msg;
			   	if (!$mail->send()) {
			       	// echo 'Mailer Error: ' . $mail->ErrorInfo;
			   	} else {
			       	// echo 'The email message was sent.';
			   	}
			}
			// end send email

			return 1;
		}
	}

	function update_upload(){
		extract($_POST);
		// var_dump($_FILES);
		$data = " title ='$title' ";
 		$data .= ", semester_id ='$semester_id' ";
		$data .= ", club_id ='$club_id' ";
		$data .= ", status ='$status' ";
		$data .= ", program_date ='$program_date' ";
		$data .= ", date_updated ='$date_updated' ";
		$data .= ", penasihat_program ='$penasihat_program' ";
		$data .= ", description ='".htmlentities(str_replace("'","&#x2019;",$description))."' ";
		$data .= ", file_json ='".json_encode($fname)."' ";

		if(isset($fname2)){
			$data .= ", file_json2 ='".json_encode($fname2)."' ";
		}

		if(empty($id)){
			$save = $this->db->query("INSERT INTO documents set $data ");
		}else{
			$save = $this->db->query("UPDATE documents set $data where id = $id");
		}
		if($save){

			// send email permohonan baru yang dikemaskini
			if($_SESSION['login_type']==2){
				$admin = $this->db->query("SELECT * FROM users where `type`= 1 LIMIT 1")->fetch_array();

				$suject = "Permohonan Kertas Kerja Baru $title - ".$_SESSION['login_firstname']." ".$_SESSION['login_lastname'];
				$msg = "Dimaklumkan bahawa terdapat permohonan baru yang dikemaskini bagi $title oleh ".$_SESSION['login_firstname']." ".$_SESSION['login_lastname'].".

Sekian, untuk makluman dan perhatian pentadbir sistem

Terima kasih.";

			    $mail = new PHPMailer;
			   	$mail->isSMTP();
			   	$mail->SMTPDebug = 0;
			   	$mail->Host = 'smtp.hostinger.com';
			   	$mail->Port = 587;
			   	$mail->SMTPAuth = true;
			   	$mail->Username = 'internbhepfpa@sippp-bhepfpa.com';
			   	$mail->Password = 'Internbhepfpa456#';
			   	$mail->setFrom('internbhepfpa@sippp-bhepfpa.com', 'SiPPP');
			   	$mail->addReplyTo('internbhepfpa@sippp-bhepfpa.com', 'SiPPP');
			   	$mail->addAddress($admin["email"], $admin["firstname"]." ".$admin["lastname"]);
			   	$mail->Subject = $suject;
			   	$mail->Body = $msg;
			   	if (!$mail->send()) {
			       	// echo 'Mailer Error: ' . $mail->ErrorInfo;
			   	} else {
			       	// echo 'The email message was sent.';
			   	}
			}
			// end send email

			return 1;
		}
	}

	function save_comment(){
		extract($_POST);
		// var_dump($_FILES);
		$data = " comment ='$comment' ";
		$data .= ", status ='$status' ";
		$data .= ", date_updated ='$date_updated' ";

		if($status=="lulus"){

			if($_FILES['approve_letter']['tmp_name'] != ''){
				$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['approve_letter']['name'];
				$move = move_uploaded_file($_FILES['approve_letter']['tmp_name'],'assets/uploads/'. $fname);
				$data .= ", approve_letter = '$fname' ";

			}
			
		}

		// send email if status update
		$doc = $this->db->query("SELECT documents.title, documents.status, users.firstname, users.lastname, users.email FROM documents JOIN users ON users.id = documents.user_id WHERE documents.id = $id")->fetch_array();
		if($doc["status"] != $status){

			$realstatus = "";
			if($status == "lulus" ){
				$realstatus = "Permohonan lulus";
			}elseif($status == "tangguh"){
				$realstatus = "Permohonan ditangguhkan";
			}elseif($status == "tolak" ){
				$realstatus = "Permohonan ditolak";
			}elseif($status == "baru" ){
				$realstatus = "Permohonan baru";
			}elseif($status == "semak" ){
				$realstatus = "Permohonan disemak";
			}elseif($status == "selesai" ){
				$realstatus = "Program selesai";
			}elseif($status == "tambahbaik" ){
				$realstatus = "Perlu penambahbaikan";
			}elseif($status == "kemas" ){
				$realstatus = "Telah Kemas Kini";
			}

			$suject = "Permohonan Kertas Kerja ".$doc["title"]." - ".$realstatus;
			$msg = "Assalamu'alaikum dan Salam Sejahtera.

Saudara/ri,

Sukacita dimaklumkan bahawa permohonan kertas kerja ".$doc["title"]." saudara/ri telah ".$realstatus.".

Sekian, untuk makluman dan perhatian saudara/ri seterusnya.

Terima kasih.

Bahagian Hal Ehwal Pelajar
Fakulti Perladangan dan Agroteknologi
Kampus Jasin Melaka";

		    $mail = new PHPMailer;
		   	$mail->isSMTP();
		   	$mail->SMTPDebug = 0;
		   	$mail->Host = 'smtp.hostinger.com';
		   	$mail->Port = 587;
		   	$mail->SMTPAuth = true;
		   	$mail->Username = 'internbhepfpa@sippp-bhepfpa.com';
		   	$mail->Password = 'Internbhepfpa456#';
		   	$mail->setFrom('internbhepfpa@sippp-bhepfpa.com', 'SiPPP');
		   	$mail->addReplyTo('internbhepfpa@sippp-bhepfpa.com', 'SiPPP');
		   	$mail->addAddress($doc["email"], $doc["firstname"]." ".$doc["lastname"]);
		   	$mail->Subject = $suject;
		   	$mail->Body = $msg;
		   	if (!$mail->send()) {
		       	// echo 'Mailer Error: ' . $mail->ErrorInfo;
		   	} else {
		       	// echo 'The email message was sent.';
		   	}
		}
		// end send email


		if(empty($comment)){
			$save = $this->db->query("UPDATE documents set $data where id = $id");
		}else{
			$save = $this->db->query("UPDATE documents set $data where id = $id");
		}
		if($save){
			return 1;
		}
	}
}