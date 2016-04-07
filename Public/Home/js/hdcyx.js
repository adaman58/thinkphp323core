var countdownHelper = function () {
    var $var,
        MIN = 60 * 1000,
        HOUR = 60 * MIN,
        DAY = 24 * HOUR

    var deadline = Date.parse('2016-04-16')

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
    countdownHelper.init()
    $('.img-support').on('click', function () {
        if (userHasSupport) {
            dm.notice('您今天已经助威过了，请您明天再来助威。')
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
                $('.fly-count-plus').addClass('in')
            } else {
                dm.notice(json.info)
            }
        })


    })
    $('.view-success').one('click', function () {
        $(this).removeClass('in')
    })
})