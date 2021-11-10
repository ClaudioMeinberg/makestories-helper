if (typeof $ === "undefined" && typeof jQuery === "function") {
    $ = jQuery;
}
$(document).on('ready', function () {
    if (typeof $ === "undefined" && typeof jQuery === "function") {
        $ = jQuery;
    }
    let sliderWidth = $('.story-curousal').width();
    let slideNo = 5;

    if (sliderWidth < 587) {
        slideNo = 1;
    } else if (sliderWidth < 587) {
        slideNo = 2;
    } else if (sliderWidth < 761) {
        slideNo = 3;
    } else if (sliderWidth < 979) {
        slideNo = 4;
    }

    // Curousal for all publishes stories
    $('.story-curousal').slick({
        slidesToShow: slideNo,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 2000,
        fade: false,
        responsive: [
            {
                breakpoint: 550,
                settings: {
                    centerMode: true,
                }
            }
        ]
    });

    $story = '';
    $default = '';

    // Get value on form submit
    $('.category-allow-form').on('submit', function (e) {
        e.preventDefault();

        $story = $('.category').val();
        $default = $('.default').val();

    });

    // Load more functionality
    let ajaxUrl = $('#ajax-posts').attr('data-ajax');
    let post_per_page = $('#ajax-posts').attr('data-posts');
    let page = 1;
    let ppp = parseInt(post_per_page);

    $("#more_posts").on("click", function () {

        // When btn is pressed.
        $(this).attr("disabled", true);

        // Disable the button, temp.
        $.post(ajaxUrl, {
            action: "more_post_ajax",
            offset: (page * ppp),
            ppp: ppp,
            beforeSend: function () {
                $('body').addClass('ms_loading');
            },
        })
            .success(function (posts) {
                let post_length = posts.length;
                if (post_length > 0) {
                    page++;
                    $("#ajax-posts").append(posts);
                    // CHANGE THIS!
                    $("#more_posts").attr("disabled", false);
                } else {
                    $('body').addClass('ms_no_more_posts');
                }
                $('body').removeClass('ms_loading');
            });
    });
})