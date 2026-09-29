<?php
	
	require 'PHPMailer/class.phpmailer.php';
    require 'PHPMailer/class.smtp.php';
    require 'PHPMailer/PHPMailerAutoload.php';
    
    $email = $_POST['email'];
   $msg = '
                <html>
                	<head>
                		<title>Subscribe us</title>  
                	</head>
                	<body>
                		<div>
                		    <p styele="color:white;">Email - '.$email.'</p>
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
        $mail-> Subject = 'Subscribe us';
        $mail-> Body = $msg;

	
    	if($mail->send()){
    	   echo 1;
    	}else{
    		
    		echo 0;	}
	

?>