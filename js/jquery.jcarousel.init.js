function kolomy_initCallback(carousel) {
    jQuery('#kolomy-next').bind('click', function() {
        carousel.next();
        return false;
    });

    jQuery('#kolomy-prev').bind('click', function() {
        carousel.prev();
        return false;
    });
};

jQuery(document).ready(function() {
    jQuery('.jcarousel').jcarousel({
       initCallback: kolomy_initCallback,
       buttonNextHTML: null,
       buttonPrevHTML: null,
    });
});
