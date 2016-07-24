<!doctype html>
<html lang="en">
<?php include 'head.html'; ?> 
<body class="flat">
<?php include 'nav.html'; ?> 

            <section class="cover fullscreen">
                <div class="background-image-holder">
                    <img alt="image" class="background-image" src="img/dog-collar.jpg">
                </div>
                <div class="container v-align-transform">
                    <div class="row">
                        <div class="col-md-6 col-sm-8">
                            <h1 class="mb40 mb-xs-16 large">What's your dog's name?</h1>
                            <form action="mydog.php">
                                <input type="text" name="name">
                            </form>
                        </div>
                    </div>

                       
                </div>
                <div class="photo-attribution">
                    Photo by <a href="https://www.flickr.com/photos/donabelandewen/">Ewen Roberts</a>, licensed under a <a href="https://creativecommons.org/licenses/by/2.0/">Creative Commons Attribution 2.0 License</a>
                </div> 
            </section>
            <section class="page-title page-banner-small bg-hot">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-12 text-center">
                            <h5 class="uppercase mb0">Don't have your own Watch Dog yet? Check out what <a href="mydog.php?name=Rocky">Rocky</a> is barking about.</h5>
                        </div>
                    </div>
                </div>
            </section>

    <?php include 'footer.html'; ?> 
    </body>
</html>