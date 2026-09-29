<?php
	
	require 'PHPMailer/class.phpmailer.php';
    require 'PHPMailer/class.smtp.php';
    require 'PHPMailer/PHPMailerAutoload.php';
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $travellers = $_POST['travellers'];
    $message= $_POST['message'];
    
    $msg = '
                <html>
                	<head>
                		<title>Get in touch</title>  
                	</head>
                	<body>
                		<div>
                		    <p styele="color:white;">Name - '.$name.'</p>
                		    <p styele="color:white;">Email - '.$email.'</p>
                		    <p styele="color:white;">Phone. -'.$phone.'</p>
                		    <p styele="color:white;">No. Of Travellers*. -'.$travellers.'</p>
                		    <p styele="color:white;">Message - '.$message.'</p>
                		</div>
                	</body>
                </html>
            
            ';	
        
        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->Host = 'smtpout.secureserver.net';
        $mail->Username = 'sales@holidaygurutravel.in';
        $mail->Password = ''; // REDACTED: credential removed from version control
        
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465; 
        // $mail->AddStringAttachment($pdf, $filename);
        
        $mail->setFrom="sales@holidaygurutravel.in";
        $mail->FromName="Holiday Guru Travel";
        // $imagePath = 'images/hddpic/'.$filename;
        // $mail->addAttachment($imagePath ,'sdfsv.pdf');
        
        // $imagePath1 = 'images/hddpic/'.$filename1;
        // $mail->addAttachment($imagePath1 ,'s.pdf');
        
        // $mail->addAddress("holidaygurutravel5@gmail.com");
        $mail->addAddress("sales@holidaygurutravel.in");
        $mail->IsHTML(true);
        $mail-> Subject = 'contact us';
        $mail-> Body = $msg;

	
    	if($mail->send()){
    	   echo 1;
    	}else{
    		
    		echo 0;	}
	

?>