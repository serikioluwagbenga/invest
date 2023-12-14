<link rel="stylesheet" href="dist/libs/owl.carousel/dist/assets/owl.carousel.min.css">

<!-- Core Css -->
<link id="themeColors" rel="stylesheet" href="dist/css/custom.css?n=992" />
<?php 

if(isset($_COOKIE['browser_theme'])) {
   switch ($_COOKIE['browser_theme']) {
    case 'dark':
        $logo = $light_logo;
        echo '<link  id="themeColors"  rel="stylesheet" href="dist/css/style-dark.min.css?n=4" />';
        $theme_btn = '<a href="index?theme=light" class=" btn btn-light text-dark rounded-pill font-medium me-2 mb-2" style="background-color: white"> <i class="ti ti-sun"></i> Change to Light Theme</a>';
        # code...
        break;
        
        default:
        $logo = $dark_logo;
        echo  '<link id="themeColors" rel="stylesheet" href="dist/css/style.min.css?n=4" />';
        $theme_btn = '<a href="index?theme=dark" class="btn btn-dark rounded-pill font-medium me-2 mb-2" for="option1"><i class="ti ti-moon"></i> Change to Dark Theme</a>';
        break;
   }
}

?>

<style>
    .bottom-nav {
        position: fixed;
        width: 100%;
        bottom: 20px;
        display: none !important;
        justify-content: center;
        z-index: 100;
    }

    @media only screen and (max-width: 970px) {
        .bottom-nav {
            display: flex !important;
        }
    }

    .botton-navs .btn {
        font-size: 25px;
    }

    aside {
        z-index: 110 !important;
    }
</style>