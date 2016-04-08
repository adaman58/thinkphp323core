var updateOrientation = function () {
    var orientation = window.orientation;
    if (orientation === 90 || orientation === 180) {
        alert('竖屏浏览效果更佳哦！')
    }
    switch (orientation) {
        case 90:
        case -90:
            orientation = "landscape";
            break;
        default:
            orientation = "portrait";
            break;
    }
}

window.addEventListener("onorientationchange" in window ? "orientationchange" : "resize", updateOrientation, false);

var countdownHelper = function () {
    var $var,
        MIN = 60 * 1000,
        HOUR = 60 * MIN,
        DAY = 24 * HOUR

    var deadline = Date.parse('2016/04/14 00:00')

    var getRemainText = function () {
        var ms = deadline - new Date().getTime()
        if (ms > DAY) {
            var d = Math.floor(ms / DAY),
                h = Math.floor((ms % DAY) / HOUR)
            return '' + d + '天' + h + '小时'
        } else if (ms > HOUR) {
            var h = Math.floor(ms / HOUR),
                m = Math.floor((ms % HOUR) / MIN)
            return '' + h + '小时' + m + '分'
        } else if (ms > 0) {
            var m = Math.floor(ms / MIN),
                s = Math.floor((ms % MIN) / 1000)
            return '' + m + '分' + s + '秒'
        } else {
            return '已结束'
        }
    }
    var tick = function () {
        var text = getRemainText()
        $var.html(text)
        if (text === '已结束') return;
        setTimeout(function () {
            tick()
        }, 1000)
    }
    return {
        init: function () {
            $var = $('.countdown')
            if ($var.length === 0) return;
            tick()
        }
    }
}()

$(function () {
    var suspendHide = 0
    countdownHelper.init()

    var suspendHideModal = function () {
        suspendHide = 1
        setTimeout(function () {
            suspendHide = 0
        }, 1000)
    }

    $('.img-support').on('click', function () {
        if (userHasSupport) {
            $('.view-tomorrow').addClass('in')
            suspendHideModal()
            return
        }
        var $this = $(this)
        $.post($this.data('voteUrl'), {
            openId: $this.data('openid'),
            myOpenId: $this.data('myopenid')
        }).then(function (json) {
            if (json.status == 1) {
                var $count = $('.support-count'),
                    count = parseInt($count.text(), 10)
                $count.html(count + 1)
                $('.view-success').addClass('in')
                userHasSupport = 1
                suspendHideModal()
                $('.fly-count-plus').addClass('in')
            } else {
                $('.view-tomorrow').addClass('in')
            }
        })
    })

    var $viewModal = $('.view-success, .view-tomorrow').on('click', function () {
        if (suspendHide) return;
        $(this).removeClass('in')
    })
    $('.btn-view-rank').on('click', function () {
        getRankingData()
        $viewModal.removeClass('in')
        $('.view-ranking').addClass('in')
    })

    var gotRankingData = function (json) {
        $('.ranking-num').html(json.active_user.rank)
        var html = '', list = json.top_list
        var r = 0
        for (var key in list) {
            if (list.hasOwnProperty(key)) {
                r += 1
                var data = list[key]
                data.r = r
                html += template('top-item', list[key])
            }
        }
        $('.ranking-list').html(html)
    }
    var getRankingData = function () {
        $.post(api.getRankingData, {
            openId: serverData.openId
        }).then(gotRankingData)
    }

})