var characterInfo = {
    a1: "张起灵",
    a2: "美国队长",
    a3: "狐狸尼克",
    a4: "银时",
    a5: "路飞",
    a6: "柯南",
    b1: "绫波丽",
    b2: "冰雪女王Anna",
    b3: "兔子朱迪",
    b4: "美少女战士",
    b5: "百变小樱",
    b6: "樱桃小丸子",
    c1: "大白",
    c2: "熊本熊",
    c3: "乔巴",
    c4: "麦兜",
    c5: "皮卡丘",
    c6: "海绵宝宝"
}
var getShareTitle = function (charactor) {
    return userInfo.nickname + "居然是" + characterInfo[charactor] + "流浪在三次元的街头，快来看看你是谁！"
}
var share = function (character) {
    var shareTitle = getShareTitle(character)
    var shareObj = {
        title: shareTitle,
        desc: shareTitle,
        link: 'http://wx.dreammove.cn/neta/index.html?nickname=' + userInfo.nickname,
        imgUrl: userInfo.headimgurl || 'http://wx.dreammove.cn/Public/Home/img/neta/share-img.jpg'
    }

    shareObj.success = function () {

    }
    shareObj.cancel = function () {

    }
    wx.ready(function () {
        wx.onMenuShareTimeline(shareObj)
        wx.onMenuShareAppMessage(shareObj)
    })
}

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
        if (!sex || !age) return;
        var rAge = (ageMean[age] || 0) + Math.random() * 4
        rAge = Math.round(rAge)
        if (rAge < 1) rAge = 1
        if (rAge > 6) rAge = 6
        var $viewResult = $('.view-result').removeClass().addClass('view view-result in ' + sex + '-' + rAge)
        setTimeout(function () {
            $viewResult.find('.link').removeClass('rise').addClass('swing')
        }, 3000)


        /*$('.view-result').css({
            "background-image": "url(/Public/Home/img/neta/result/" + sex + "-" + rAge + ".jpg)"
        })*/
        share('' + sex + rAge)
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