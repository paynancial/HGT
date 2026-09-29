<?php require_once __DIR__ . '/site_config.php'; ?>
    <link rel="icon" type="image/png" sizes="192x192" href="assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&amp;family=Manrope:wght@200..800&amp;family=Montez&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/css/magnific-popup.min.css">
    <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hg-site.css">

    
    
    <div class="th-menu-wrapper onepage-nav">
        <div class="th-menu-area text-center"><button class="th-menu-toggle"><i class="fal fa-times"></i></button>
            <div class="mobile-logo bg-white"><a href="/"><img src="assets/img/holidaygurulogo.jpg" alt="holidaygurulogo.jpg"></a></div>
            <div class="th-mobile-menu">
              <ul>
                                    
                                    <li><a href="/">Home</a></li>
                                    <li class="menu-item-has-children"><a href="#">Category</a>
                                        <ul class="sub-menu">
                                            <li><a href="/international-holidays">International Holidays</a></li>
                                            <li><a href="/religious-tour">Religious Tour</a></li>
                                            <li><a href="/domestic-holidays">Domestic Holidays</a></li>
                                        </ul>
                                    </li>
                                    <li class="menu-item-has-children"><a href="#">Themes</a>
                                        <ul class="sub-menu">
                                            <li><a href="/family-holiday">Family Holiday</a></li>
                                            <li><a href="/beach-holiday">Beach Holiday</a></li>
                                            <li><a href="/hill-station-holidays">Hill Station Holidays</a></li>
                                            <li><a href="/honeymoon-holiday">Honeymoon Holiday</a></li>
                                            <li><a href="/pilgrim-holidays">Pilgrim Holidays</a></li>
                                            <li><a href="/adventure-holiday">Adventure Holiday</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="/destinations">Destinations</a></li>
                                    
                                    <li><a href="/about">About Us</a></li>
                                    <li><a href="/contact">Contact Us</a></li>
                                    <li><a href="/service">Services</a></li>
                                </ul>
            </div>
        </div>
    </div>
    <header class="th-header header-layout1">
        <div class="header-top">
            <div class="container th-container">
                <div class="row justify-content-center justify-content-xl-between align-items-center">
                    <div class="col-auto d-none d-md-block">
                        <div class="header-links">
                            
                            <ul>
                                <li class="d-none d-xl-inline-block"><i class="fa-regular fa-envelope" style="color: #000000;"></i>
                                <span><a href="<?= hg_e(hg_mailto_href()) ?>" class="info-box_link"><?= hg_e(HG_EMAIL_DISPLAY) ?></a></span></li>
                                <li class="d-none d-xl-inline-block"><i class="fa-solid fa-phone" style="color: #000000;"></i><span><a href="<?= hg_e(hg_tel_href()) ?>" class="info-box_link" aria-label="Call Holiday Guru Travel on <?= hg_e(HG_PHONE_DISPLAY) ?>"><?= hg_e(HG_PHONE_DISPLAY) ?></a></span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="header-right">
                           
                           <div class="header-links">
                                <ul>
                                    <li class="d-none d-md-inline-block"><a><b>( A unit of Swaasthik Vocation Pvt. Ltd. )</b></a></li>
                                    <li class="d-none d-md-inline-block"><button class="button-18" role="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Quick Enquiry</button></li>
                                    
                                    
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="sticky-wrapper">
            
            <div class="menu-area">
                <div class="container th-container">
                    <div class="row align-items-center justify-content-between">
                        <div class=" col-md-3 col-sm-3 col-lg-3 col-9">
                            <div class="header-logo"><a href="/"><img src="assets/img/holidaygurulogo.jpg"
                                        alt="holidaygurulogo.jpg"></a></div>
                        </div>
                        <div class=" col-md-9 col-sm-9 col-lg-9 col-3">
                            <nav class="main-menu d-none  d-xl-block">
                                <ul>
                                    
                                    <li><a href="/">Home</a></li>
                                    <li class="menu-item-has-children"><a href="#">Category</a>
                                        <ul class="sub-menu">
                                            <li><a href="/international-holidays">International Holidays</a></li>
                                            <li><a href="/religious-tour">Religious Tour</a></li>
                                            <li><a href="/domestic-holidays">Domestic Holidays</a></li>
                                        </ul>
                                    </li>
                                    <li class="menu-item-has-children"><a href="#">Themes</a>
                                        <ul class="sub-menu">
                                            <li><a href="/family-holiday">Family Holiday</a></li>
                                            <li><a href="/beach-holiday">Beach Holiday</a></li>
                                            <li><a href="/hill-station-holidays">Hill Station Holidays</a></li>
                                            <li><a href="/honeymoon-holiday">Honeymoon Holiday</a></li>
                                            <li><a href="/pilgrim-holidays">Pilgrim Holidays</a></li>
                                            <li><a href="/adventure-holiday">Adventure Holiday</a></li>
                                        </ul>
                                    </li>
                                    <li><a href="/destinations">Destinations</a></li>
                                    
                                    <li><a href="/about">About Us</a></li>
                                    <li><a href="/contact">Contact Us</a></li>
                                    <li><a href="/service">Services</a></li>
                                </ul>
                            </nav><button type="button" class="th-menu-toggle d-block  d-xl-none" style="float:right;"><i
                                    class="far fa-bars"></i></button>
                        </div>
                        
                    </div>
                </div>
                <div class="" data-mask-src="assets/img/logo_bg_mask.png"></div>
            </div>
        </div>
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h2 class="modal-title fs-5" id="staticBackdropLabel">Book a Tour</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark fa-2xl" style="color: #000000;"></i></button>
      </div>
      <div class="modal-body">
        <div>
     <form id="contactForm2" class="contact-form style2 ajax-contact ">

                            <div class="row">
                                <div class="col-12 form-group">
                                    <input type="text" class="form-control" name="name" oninput="validateUsername(this)"
                                        id="name3" placeholder=" Full Name" required>
                                    <img src="assets/img/icon/user.svg" alt="">
                                </div>
                                <div class="col-12 form-group"><input type="tel" class="form-control" name="phone"
                                        oninput="validateNumeric(this)" maxlength="10" id="phone3"
                                        placeholder="Phone Number" required>
                                    <img src="assets/img/icon/phone1.png" width="20px" alt="img">
                                </div>
                                <div class="col-12 form-group">
                                    <input type="email" class="form-control" name="email" id="email3"
                                        placeholder="Your Mail" required>
                                    <img src="assets/img/icon/mail.svg" alt="">
                                </div>
                                <div class="form-group col-12">
                                    <select name="travellers" id="subject" class="form-select nice-select" required>
                                        <option value="" selected="selected" disabled="disabled">No. Of Travellers*
                                        </option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5+</option>


                                    </select>

                                </div>
                                <div class="form-group col-12"><textarea name="message" id="message" cols="30" rows="3"
                                        class="form-control" placeholder="Your Message" required></textarea> <img
                                        src="assets/img/icon/chat.svg" alt=""></div>
                                <div class="form-btn col-12 mt-24"><button id="submit" type="submit" class="th-btn style3">Send
                                        message <img src="assets/img/icon/plane.svg" alt=""></button></div>
                            </div>
                            
                        </form>
</div>
      </div>
      
    </div>
  </div>
</div>
    </header>