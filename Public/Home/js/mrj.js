$(function () {
    var share = function (bmi, bmi_text, value) {
        var shareObj
        if (bmi_text === 'thin') {
            shareObj = {
                title: userInfo.nickname + '的测试结果：瘦得太可怜惹，跪求土豪胖子来包养！',
                desc: userInfo.nickname + '的测试结果：瘦得太可怜惹，跪求土豪胖子来包养！',
                link: 'http://wx.dreammove.cn/mrj/index.html?nickname=' + userInfo.nickname + '&bmi=' + bmi,
                imgUrl: 'http://wx.dreammove.cn/Public/Home/img/mrj/icon-share-' + bmi_text + '.jpg'
            }
        } else if (bmi_text === 'normal') {
            shareObj = {
                title: userInfo.nickname + '的测试结果：身材标准，我的美让我错过了卖肉赚钱的好机会！',
                desc: userInfo.nickname + '的测试结果：身材标准，我的美让我错过了卖肉赚钱的好机会！',
                link: 'http://wx.dreammove.cn/mrj/index.html?nickname=' + userInfo.nickname + '&bmi=' + bmi,
                imgUrl: 'http://wx.dreammove.cn/Public/Home/img/mrj/icon-share-' + bmi_text + '.jpg'
            }
        } else if (bmi_text === 'fat') {
            shareObj = {
                title: userInfo.nickname + '的测试结果：身价' + value + ' 在用肉换钱的路上我胖着就把钱给挣了！',
                desc: userInfo.nickname + '的测试结果：身价' + value + ' 在用肉换钱的路上我胖着就把钱给挣了！',
                link: 'http://wx.dreammove.cn/mrj/index.html?nickname=' + userInfo.nickname + '&bmi=' + bmi + '&fv=' + value,
                imgUrl: 'http://wx.dreammove.cn/Public/Home/img/mrj/icon-share-' + bmi_text + '.jpg'
            }
        } else {

        }
        if (shareObj) {
            shareObj.success = function () {
                $('.modal-share').modal('hide')
            }
            shareObj.cancel = function () {
                $('.modal-share').modal('hide')
            }
            wx.ready(function () {
                wx.onMenuShareTimeline(shareObj)
                wx.onMenuShareAppMessage(shareObj)
            })
        }
    }


    var viewHelper = function () {
        var $views, $active, viewCount
        return {
            init: function () {
                $views = $('.view')
                viewCount = $views.length
                $active = $views.filter('in')
                if (!$active.length) {
                    $active = $views.eq(0)
                }
            }, next: function () {
                var index = $active.index($views)
                if (index + 1 >= viewCount) return;
                $views.removeClass('in')
                $active = $views.eq(index + 1).addClass('in')
                if (index === 1) {
                    checkWidth()
                }
            }, prev: function () {
                var index = $active.index($views)
                if (index <= 0) return;
                $views.removeClass('in')
                $active = $views.eq(index - 1).addClass('in')
            }

        }
    }()

    viewHelper.init()

    // 计算一下
    $('.btn-calculate').on('click', function () {
        var height = parseInt($('.input-height').val(), 10) / 100,
            weight = parseInt($('.input-weight').val(), 10)

        if (!height || !weight) {
            dm.notice('填错了')
            return
        }

        var bmi = weight / (height * height),
            bmi_text

        bmi = bmi.toFixed(2)
        var value = ''

        if (bmi <= 18.5) bmi_text = 'thin'
        else if (bmi < 23) bmi_text = 'normal'
        else {
            bmi_text = 'fat'
            var weight_ceil = height * height * 21;
            value = (weight - weight_ceil) * 2 * 200 * 100
            value = Math.ceil(value / 10000) + '万'
        }


        $('.bmi').html(bmi)
        $('.fat-value').html(value)
        $('.view-result').addClass(bmi_text)


        share(bmi, bmi_text, value)
        var $modal = $('.modal-loading').modal()
        setTimeout(function () {
            $modal.modal('hide')
            viewHelper.next()
        }, Math.random() * (9000 - 4000) + 4000)
    })

    // 分享一下
    $('.btn-share').on('click', function () {
        $('.modal-share').modal()
    })
})