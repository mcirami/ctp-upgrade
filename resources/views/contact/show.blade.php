<!DOCTYPE html>

<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/ico"
          href="<?php echo $webroot . "/" . \LeadMax\TrackYourStats\System\Company::loadFromSession()
                  ->getImgDir() . "/favicon.ico?v=2"; ?>"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/default.css?v=3.5"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/external-header.css?v=1"/>
    <link rel="stylesheet" href="<?php echo $webroot; ?>css/company.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/main.js?v=2.2"></script>
    <script type="text/javascript" src="<?php echo $webroot; ?>js/external-header.js?v=1"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</head>
<body class="contact">

<header id="home" class="full_width external">
    <div class="container">
        <div class="row_wrap">
            <div class="nav_wrap external_nav">
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <a class="navbar-brand" href="/">
                            <img src="{{ $webroot.\LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() .  "/logo.png"}}" alt="">
                        </a>

                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav">
                                <li class="nav-item">
                                    <a class="nav-link" aria-current="page" href="<?php echo $webroot; ?>#about">About Us</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo $webroot; ?>#our_benefits">How It Works</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo $webroot; ?>#tabs">Passive Income</a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo $webroot; ?>contact" class="nav-link">Contact</a>
                                </li>
                            </ul>
                            <div class="buttons_wrap">
                                <a class="button white" href="<?php echo $webroot; ?>login.php">Login</a>
                                <a class="button value_span11 value_span2 value_span4" href="<?php echo $webroot; ?>signup.php?mid=1003">Sign Up</a>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>
<section class="full_width header_padding">
    <div class="container">
        <div class="heading_holder">
            <span class="lft value_span9">Contact</span>
        </div>
        <div class="errors">
            @if(session()->has('success'))
                <div class="alert alert-success">
                    <h3>{{ session()->get('success') }}</h3>
                </div>
            @endif
            @if(session()->has('error'))
                <div class="alert alert-danger">
                    <h3>{{ print_r(session()->get('error'), true) }}</h3>
                </div>
            @endif
        </div>
        <div class="two_columns external">
            <div class="column">
                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input class="form-control" type="text" name="name" placeholder="Name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input class="form-control" type="email" name="email" placeholder="Email Address" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country:</label>
                        @include('components.country-dropdown')
                    </div>
                    <div class="form-group">
                        <label for="im_type">Instant Messenger:</label>
                        <select class="selectBox w-100" name="im_type" id="im_type" required>
                            <option value="">Select Instant Messenger</option>
                            <option value="skype">Skype</option>
                            <option value="telegram">Telegram</option>
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="im_username">IM Username:</label>
                        <input class="form-control" type="text" name="im_username" placeholder="IM Username" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Your Message:</label>
                        <textarea rows="5" class="form-control" name="message" placeholder="Your Message" required></textarea>
                    </div>
                    <button class="button value_span11 value_span2 value_span4" type="submit">Send</button>
                </form>
            </div>
            <div class="column">
                <h3>One Link. Unlimited Cash.</h3>
                <div class="image_wrap">
                    <img src="<?php echo $webroot; ?>images/mc-icon-circle.png" alt="">
                </div>
            </div>
        </div>
    </div>
</section>
<footer class="full_width external">
    <div class="container">
        <div class="footer_content">
            <div class="logo_wrap">
                <a href="/">
                    <img src="{{ $webroot.\LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() .  "/logo.png"}}" alt="">
                </a>
            </div>
            <ul>
                <li>
                    <a href="<?php echo $webroot; ?>#home">About Us</a>
                </li>
                <li>
                    <a href="<?php echo $webroot; ?>#our_benefits">How It Works</a>
                </li>
                <li>
                    <a href="<?php echo $webroot; ?>#tabs">Passive Income</a>
                </li>
                <li>
                    <a href="<?php echo $webroot; ?>contact">Contact</a>
                </li>
            </ul>
            <div class="buttons_wrap">
                <a class="button white mr-4" href="<?php echo $webroot; ?>login.php">Login</a>
                <a class="button value_span11 value_span2 value_span4" href="<?php echo $webroot; ?>signup.php">Sign Up</a>
            </div>
        </div>
        <p class="copy">&copy; model.cash | All rights reserved.</p>
    </div>
</footer>
</body>
</html>
