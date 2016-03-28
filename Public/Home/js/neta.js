$(function () {
    var $views = $('.view')
    $views.find('.next').on('click', function () {
        var $thisView = $(this).closest('.view')
        var $thisOptions = $thisView.find('.option')
        if ($thisOptions.length) {
            var $thisActive = $thisOptions.filter('.active')
            if (!$thisActive.length) {
                $thisOptions.removeClass('rise').addClass('shake').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function () {
                    $(this).removeClass('shake')
                })
                return
            }
        }
        $views.removeClass('in')
        $thisView.next().addClass('in')
    })

    var $options = $views.find('.option')
    $options.on('click', function () {
        $(this).addClass('active').siblings().removeClass('active')
    })

    var ageMean = {
        a: 1,
        b: 2,
        c: 3
    }
    $('.btn-result').on('click', function () {
        var sex = $('.view-sex .active').data('value')
        var age = $('.view-age .active').data('value')
        var rAge = (ageMean[age] || 0) + Math.random() * 4
        rAge = Math.round(rAge)
        if (rAge < 0) rAge = 0
        if (rAge > 6) rAge = 6
        $('.view-result').removeClass().addClass('view view-result in ' + sex + '-' + rAge)
    })

    var loaded = false
    var loadEvent = function () {
        if (!loaded) {
            loaded = true
            $('.view').removeClass('in')
            $('.view-index').addClass('in')
        }
    }

    setTimeout(function () {
        loadEvent()
    }, 5000)
    $(window).on('load', function () {
        setTimeout(function () {
            loadEvent()
        }, 2000)
    })
})