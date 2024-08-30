$(function () {

    //On hover of the image, show the video and hide the image
    $('.kl-see').hover(function () {

        //check for the nearest '.kl-img-xts img.img-fluid' and hide it
        if ($(this).closest('.kl-img-xts').find('img.img-fluid').next().is('video')) {
            $(this).closest('.kl-img-xts').find('img.img-fluid').hide();
            $(this).closest('.kl-img-xts').find('img.img-fluid').next().css('display', 'block');
        }
    }, function () {
        if ($(this).closest('.kl-img-xts').find('img.img-fluid').next().is('video')) {
            $(this).closest('.kl-img-xts').find('img.img-fluid').show();
            $(this).closest('.kl-img-xts').find('img.img-fluid').next().hide();
        }
    });

});