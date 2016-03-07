
if (typeof jQuery === "undefined" || jQuery === null) {
    throw new Error('modal.js requires jQuery')
}

if (Modernizr.csstransitions == null) {
    throw new Error('modal.js requires Modernizr.csstransitions')
}

(function ($) {
    'use strict';
    var transitionEnd = (function () {
        var el, key, transEndEventNames, value;
        el = document.createElement('div');
        transEndEventNames = {
            WebkitTransition: 'webkitTransitionEnd',
            MozTransition: 'transitionend',
            OTransition: 'oTransitionEnd otransitionend',
            transition: 'transitionend'
        };
        for (key in transEndEventNames) {
            value = transEndEventNames[key];
            if (el.style[key] != null) {
                return value;
            }
        }
        return false;
    })()
    $.fn.emulateTransitionEnd = function (duration) {
        var $el, callback, called;
        called = false;
        $el = this;
        $el.one('dmTransitionEnd', function () {
            return called = true;
        });
        callback = function () {
            if (!called) {
                return $el.trigger(transitionEnd || '');
            }
        };
        setTimeout(callback, duration);
        return this;
    };
    $(function () {
        if (!transitionEnd) {
            return;
        }
        $.event.special.dmTransitionEnd = {
            bindType: transitionEnd,
            delegateType: transitionEnd,
            handle: function (e) {
                if ($(e.target).is(this)) {
                    return e.handleObj.handler.apply(this, arguments);
                }
            }
        };
    });
})(jQuery);

!function ($) {
    var TRANSITION_DURATION = 150
        , BACKDROP_TRANSITION_DURATION = 150
        , DEFAULT = {
        backdrop: true,
        keyboard: true,
        show: true,
        backdropColor: ''
    }
    var Modal = (function () {
        function Modal(elm, opt) {
            var that = this
            this.options = opt
            this.$body = $(document.body)
            this.$modal = $(elm)
            this.$dialog = this.$modal.find('.modal-dialog')
            this.$backdrop = null
            this.isShown = null
            this.originalBodyPad = null
            this.scrollbarWidth = 0
            this.ignoreBackdropClick = false
            if (this.options.remote) {
                this.$modal.find('.modal-content').load(this.options.remote, function () {
                    return that.$modal.trigger('load')
                })
            }
        }

        Modal.prototype.toggle = function () {
            if (this.isShown) {
                this.hide()
            } else {
                this.show()
            }
            return this
        }

        Modal.prototype.show = function () {
            var $body, openModalsCount, showEvent, that
            that = this
            showEvent = $.Event('show')
            this.$modal.trigger(showEvent)
            if (this.isShown || showEvent.isDefaultPrevented()) {
                return
            }
            this.isShown = true
            this.checkScrollbar().setScrollbar()
            this.$body.addClass('modal-open')
            this.escapeEvent().resizeEvent()
            $body = this.$body
            openModalsCount = $body.data('openModals')
            if (openModalsCount != null) {
                openModalsCount += 1
                $body.data('openModals', openModalsCount)
            } else {
                openModalsCount = 1
                $body.data('openModals', 1)
            }
            this.$modal.css('z-index', 1040 + 10 * openModalsCount)
            this.$modal.on('click.dismiss', '[data-dismiss="modal"]', function () {
                return that.hide()
            })
            this.$dialog.on('mousedown.dismiss', function () {
                return that.$modal.one('mouseup.dismiss', function (e) {
                    if ($(e.target).is(that.$modal)) {
                        return that.ignoreBackdropClick = true
                    }
                })
            })
            this.backdrop(function () {
                var shownEvent, transition
                transition = Modernizr.csstransitions && that.$modal.hasClass('fade')
                if (!that.$modal.parent().length) {
                    that.$modal.appendTo(that.$body)
                }
                that.$modal.show().scrollTop(0)
                that.adjustDialog()
                if (transition) {
                    that.$modal[0].offsetWidth
                }
                that.$modal.addClass('in')
                that.enforceFocus()
                shownEvent = $.Event('shown')
                if (transition) {
                    that.$dialog.one('dmTransitionEnd', function () {
                        that.$modal.trigger('focus').trigger(shownEvent)
                    }).emulateTransitionEnd(TRANSITION_DURATION)
                } else {
                    that.$modal.trigger('focus').trigger(shownEvent)
                }
            })
            return this
        }

        Modal.prototype.hide = function () {
            var e = $.Event('hide')
            this.$modal.trigger(e)
            if (!this.isShown || e.isDefaultPrevented()) {
                return
            }
            this.isShown = false
            this.escapeEvent().resizeEvent()
            this.$body.data('openModals', this.$body.data('openModals') - 1)
            $(document).off('focusin.modal')
            this.$modal.removeClass('in').off('click.dismiss')
            if (Modernizr.csstransitions && this.$modal.hasClass('fade')) {
                this.$modal.one('dmTransitionEnd', $.proxy(this.hideModal, this)).emulateTransitionEnd(TRANSITION_DURATION)
            } else {
                this.hideModal()
            }
            return this
        }

        Modal.prototype.enforceFocus = function () {
            var that = this
            $(document).off('focusin.modal').on('focusin.modal', function (e) {
                if (that.$modal[0] !== e.target && !that.$modal.has(e.target).length) {
                    return that.$modal.trigger('focus')
                }
            })
            return this
        }

        Modal.prototype.escapeEvent = function () {
            var that
            that = this
            if (this.isShown && this.options.keyboard) {
                this.$modal.on('keydown.dismiss', function (e) {
                    e.which === 27 && that.hide()
                })
            } else if (!this.isShown) {
                that.$modal.off('keydown.dismiss')
            }
            return this
        }

        Modal.prototype.resizeEvent = function () {
            var that
            that = this
            if (this.isShown) {
                $(window).on('resize.modal', function () {
                    return that.handleUpdate()
                })
            } else {
                $(window).off('resize.modal')
            }
            return this
        }

        Modal.prototype.hideModal = function () {
            var that
            that = this
            this.$modal.hide()
            this.backdrop(function () {
                if (that.$body.data('openModals') === 0) {
                    that.$body.removeClass('modal-open')
                    that.resetAdjustments().resetScrollbar()
                }
                return that.$modal.trigger('hidden')
            })
            return this
        }

        Modal.prototype.removeBackdrop = function () {
            this.$backdrop && this.$backdrop.remove()
            this.$backdrop = null
            return this
        }

        Modal.prototype.backdrop = function (callback) {
            var _animateName, _backdropColor, callbackRemove, doAnimate, that
            that = this
            _animateName = this.$modal.hasClass('fade') ? ' fade' : ''
            if (this.isShown && this.options.backdrop) {
                doAnimate = Modernizr.csstransitions && _animateName
                _backdropColor = this.options.backdropColor || ''
                that.lastVisibleModal = $('.modal-backdrop').filter('.in').removeClass('in')
                this.$backdrop = $('<div class="modal-backdrop ' + _animateName + ' ' + _backdropColor + '"></div>').css('z-index', this.$body.data('openModals') * 10 + 1039).appendTo(this.$body)
                this.$modal.on('click.dismiss', function (e) {
                    if (that.ignoreBackdropClick) {
                        that.ignoreBackdropClick = false
                        return
                    }
                    if (e.target !== e.currentTarget) return;
                    that.options.backdrop === 'static'
                        ? that.$modal[0].focus()
                        : that.hide()

                })
                if (doAnimate) {
                    this.$backdrop[0].offsetWidth
                }
                this.$backdrop.addClass('in')
                if (!callback) {
                    return
                }
                if (doAnimate) {
                    this.$backdrop.one('dmTransitionEnd', callback).emulateTransitionEnd(BACKDROP_TRANSITION_DURATION)
                } else {
                    callback()
                }
            } else if (!this.isShown && this.$backdrop) {
                this.$backdrop.removeClass('in')
                that.lastVisibleModal.addClass('in')
                callbackRemove = function () {
                    that.removeBackdrop()
                    return callback && callback()
                }
                if (Modernizr.csstransitions && this.$modal.hasClass('fade')) {
                    this.$backdrop.one('dmTransitionEnd', callbackRemove).emulateTransitionEnd(BACKDROP_TRANSITION_DURATION)
                } else {
                    callbackRemove()
                }
            } else if (callback) {
                callback()
            }
            return this
        }

        Modal.prototype.handleUpdate = function () {
            this.adjustDialog()
            return this
        }

        Modal.prototype.adjustDialog = function () {
            var modalIsOverflowing
            modalIsOverflowing = this.$modal[0].scrollHeight > document.documentElement.clientHeight
            this.$modal.css({
                paddingLeft: !this.bodyIsOverflowing && modalIsOverflowing ? this.scrollbarWidth : '',
                paddingRight: this.bodyIsOverflowing && !modalIsOverflowing ? this.scrollbarWidth : ''
            })
            this.$dialog.css('margin-top', this.$modal.data('mt') || (document.documentElement.clientHeight - this.$dialog.outerHeight()) / 2 - 30)
            return this
        }

        Modal.prototype.resetAdjustments = function () {
            this.$modal.css({
                paddingLeft: '',
                paddingRight: ''
            })
            return this
        }

        Modal.prototype.checkScrollbar = function () {
            var documentElementRect, fullWindowWidth
            fullWindowWidth = window.innerWidth
            if (!fullWindowWidth) {
                documentElementRect = document.documentElement.getBoundingClientRect()
                fullWindowWidth = documentElementRect.right - Math.abs(documentElementRect.left)
            }
            this.bodyIsOverflowing = document.body.clientWidth < fullWindowWidth
            this.scrollbarWidth = this._measureScrollbar()
            return this
        }

        Modal.prototype.setScrollbar = function () {
            var bodyPad = parseInt(this.$body.css('padding-right') || 0, 10)
            this.originalBodyPad = document.body.style.paddingRight || ''
            if (this.bodyIsOverflowing) {
                this.$body.css('padding-right', bodyPad + this.scrollbarWidth)
            }
            return this
        }

        Modal.prototype.resetScrollbar = function () {
            this.$body.css('padding-right', this.originalBodyPad)
            return this
        }

        Modal.prototype._measureScrollbar = function () {
            var scrollDiv, scrollbarWidth
            scrollDiv = document.createElement('div')
            scrollDiv.className = 'modal-scrollbar-measure'
            this.$body.append(scrollDiv)
            scrollbarWidth = scrollDiv.offsetWidth - scrollDiv.clientWidth
            this.$body[0].removeChild(scrollDiv)
            return scrollbarWidth
        }

        return Modal

    })()
    $.fn.modal = function (option, btn) {
        return this.each(function () {
            var $this = $(this)
                , options = $.extend({}, DEFAULT, $this.data(), typeof option === 'object' && option)
                , _modal = $this.data('modal')
            if (!_modal) {
                $this.data('modal', (_modal = new Modal(this, options)))
            }
            if (typeof option === 'string') _modal[option](btn)
            else if (options.show) _modal.show(btn)
        })
    }
    $.fn.modal.Constructor = Modal
    $(document).on('click.modal', '[data-toggle="modal"]', function (e) {
        var $this = $(this)
            , href = $this.attr('href')
            , $modal = $($this.data('target') || (href && href.replace(/.*(?=#\S+$)/, '')))
            , option = $modal.data('modal') ? 'toggle' : $.extend({remote: !/#/.test(href) && href}, $modal.data(), $this.data())
        if ($this.is('a')) e.preventDefault()
        $modal.modal(option)
    })
}(jQuery)

window.dmAlert = function (msg, callback) {
    var $modal = $('<div class="modal modal-alert fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">提示<button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn-primary" data-dismiss="modal">确定</button></div></div></div></div>').appendTo($(document.body))
    $modal.on('hidden', function () {
        $modal.remove()
        if (callback) callback()
    }).on('shown', function () {
        $modal.find('button').focus()
    })
    $modal.find('.modal-body').html(msg)
    return $modal.modal()
}
window.dmConfirm = function (msg, options, yes, no, cancel) {
    var defaultOption = {
        yes: '确定',
        no: '取消'
    }
    if ($.isFunction(options)) {
        cancel = no
        no = yes
        yes = options
    }
    if (cancel == null) cancel = no

    var o = $.extend({}, defaultOption, options)
    var $modal = $('<div class="modal modal-confirm fade" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header">提示<button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn-primary confirm" data-dismiss="modal">' + o.yes + '</button><button type="button" class="btn-o cancel" data-dismiss="modal">' + o.no + '</button></div></div></div></div>').appendTo($(document.body))
    $modal.on('shown', function () {
        $modal.find('.confirm').focus()
    }).on('hidden', function () {
        $modal.remove()
        if (cancel) cancel()
    })
    $modal.on('click', '.confirm', function (e) {
        e.stopPropagation()
        $modal.remove()
        if (yes) yes()
    }).on('click', '.cancel', function (e) {
        e.stopPropagation()
        $modal.remove()
        if (no) no()
    })
    $modal.find('.modal-body').html(msg)
    return $modal.modal()
}
dm.alert = window.dmAlert
dm.confirm = window.dmConfirm

dm.notice = function (msg, callback) {
    var $notice = $('<div class="dm-notice"><div class="note">' + msg + '</div></div>').appendTo('body')
    $notice[0].offsetWidth
    $notice.addClass('in')
    setTimeout(function () {
        $notice.removeClass('in').one('dmTransitionEnd', function () {
            $(this).remove()
            callback && callback()
        }).emulateTransitionEnd(400)
    }, Math.max($('<u>' + msg + '</u>').text().length, 10) * 300)
}

!function ($) {
    $.fn.note = function (msg) {

        return this.each(function () {
            var $this = $(this)
            $this.on('click.dismiss', '[data-dismiss=note]', function () {
                $this.hide()
            })
            $this.find('.html').html(msg)
            $this.fadeIn()
        })
    }
}(jQuery)

dm.loading = function (action) {
    var $modal = $('.modal-loading')
    if (action == null) action = ''
    if (action === 'hide') {
        $modal.modal('hide')
    } else {
        if ($modal.length) {
            $modal.find('var').html(action || '')
        }
        else {
            $modal = $('<div class="modal modal-loading fade"><div class="modal-dialog"><var>' + action + '</var></div></div>').appendTo(document.body)
            $modal.on('hidden', function () {
                $modal.remove()
            })
            $modal.modal({
                backdrop: 'static',
                backdropColor: 'white'
            })
        }
    }
}

!function ($) {
    return $.fn.loading = dm.loading
}(jQuery)