<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>TAWallet</title>
        <link rel="stylesheet" href="styles.css">
    </head>

    <body id="particles-js" class="fullbkg" background = "normalBackground.jpg">
        <div class="animated bounceInDown">
            <div class="regContainer">
                <span class="error animated tada" id="msg"></span>
                <form name="form1" class="regBox" method='POST'>
                    <h2>TAWallet</h2>
                    <h4>Portflio<span> Sign Up</span></h4>
                    <h5>Sign up for your account</h5>
                        <input type="text" name="name" placeholder="Full Name" autocomplete="off">
                        <i class="typcn typcn-eye" id="eye"></i>
                        <input type="text" name="email" placeholder="Email" autocomplete="off">
                        <input type="password" name="password" placeholder="Password" id="pwd" autocomplete="off">
                        <input type="password" name="confPassword" placeholder="Confirm Password" id="pwd" autocomplete="off">
                        <input type="submit" value="Sign Up" class="regBtn1">
                </form>
                <a href="#" class="returnLogin" onclick="location.href='index.html'">Return to Login</a>
            </div>
        </div>   
    </body>

</html>