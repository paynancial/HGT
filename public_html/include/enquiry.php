<div class="col-xxl-4 col-lg-5">
                    <aside class="sidebar-area style3">
                        
                        <form id="contactForm3" class="contact-form style2 ajax-contact ">

                            <div class="row">
                                <h4 class="text-center">Book Your Tour Now</h4>
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
                        
                    </aside>
                </div>