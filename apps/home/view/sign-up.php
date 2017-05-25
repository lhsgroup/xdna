<?php
$this->layout = 'login';
?>
<div class="outer">
    <div class="inner">
        <div class="row">
            <div class="col-md-6 col-sm-8 col-md-offset-3 col-sm-offset-2 form-wrap">
                <div class="text-center logo-wrap">
                    <a href="/"><img src="/images/dark-logo.svg" alt="" width="125"></a>
                </div> <!-- end text-center -->

                <form method="post">
                    <div class="form-group col-md-6">
                        <label for="firstname">First Name</label>
                        <input type="text" class="form-control" id="firstname" placeholder="First Name" required="">
                    </div> <!-- end form-group -->
                    <div class="form-group col-md-6">
                        <label for="lastname">Last Name</label>
                        <input type="text" class="form-control" id="lastname" placeholder="Last Name" required="">
                    </div> <!-- end form-group -->
                    <div class="form-group col-md-12">
                        <label for="exampleInputEmail1">Email Address</label>
                        <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email" required="">
                    </div> <!-- end form-group -->
                    <div class="form-group col-md-12">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Password" required="">
                    </div> <!-- end form-group -->

                    <div class="text-center col-md-12 mt10 mb20">
                        <button type="submit" class="btn se-btn btn-rounded">Submit</button>
                    </div> <!-- end text-center -->
                </form> <!-- end form -->



                <div class="col-sm-12 text-center">
                    <p class="text-muted mbn">Already Registered? <a href="/login/">Login here!</a></p>
                </div>
            </div>
        </div> <!-- end row -->
    </div> <!-- end inner -->
</div> <!-- end outer -->