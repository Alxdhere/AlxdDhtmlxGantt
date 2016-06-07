var generic = {
    '_render_grid_header' : gantt._render_grid_header,
    '_render_grid_item' : gantt._render_grid_item,
};

gantt.config.scale_height_auto = false;
gantt.config.filter = false;
gantt.config.filters = [];

gantt._render_grid_header = function () {
    var columns = this.getGridColumns();
    var filters = this.config.filters;
    var title_cells = [];
    var filter_cells = [];
    var width = 0,
        labels = this.locale.labels;

    var lineHeigth = this.config.scale_height - 2;

    for (var i = 0; i < columns.length; i++) {
        var last = i == columns.length - 1;
        var col = columns[i];
        var colWidth = col.width*1;
        if (last && this._get_grid_width() > width + colWidth)
            col.width = colWidth = this._get_grid_width() - width;
        width += colWidth;
        var sort = (this._sort && col.name == this._sort.name) ? ("<div class='gantt_sort gantt_" + this._sort.direction + "'></div>") : "";
        var cssClass = ["gantt_grid_head_cell",
            ("gantt_grid_head_" + col.name),
            (last ? "gantt_last_cell" : ""),
            this.templates.grid_header_class(col.name, col)].join(" ");

        var style = "width:" + (colWidth - (last ? 1 : 0)) + "px;";
        var label = (col.label || labels["column_" + col.name]);
        label = label || "";
        var title_cell = "<div class='" + cssClass + "' style='" + style + "' column_id='" + col.name + "'>" + label + sort + "</div>";
        title_cells.push(title_cell);

        if (filters.length >= i) {
            var filter = filters[i];
            var filter_cell = "<div class='" + cssClass + "' style='" + style + "'>" + filter.control + "</div>";
            filter_cells.push(filter_cell);
        }
    }

    this.$grid_scale.innerHTML = "<div class='gantt_grid_scale_row'>" + title_cells.join("") + "</div>" + (this.config.filter ? "<div class='gantt_grid_scale_row'>" + filter_cells.join("") + "</div>" : "");
    this.$grid_scale.style.width = (width - 1) + "px";

    if (this.config.scale_height_auto == true) {
        var $grid_scale = $(this.$grid_scale);
        $grid_scale.removeAttr("style");
        this.config.scale_height = $grid_scale.height();
        this.$grid_scale.style.height = (this.config.scale_height - 1) + "px";
        this.$grid_scale.style.lineHeight = "1.42857143";
    } else {
        this.$grid_scale.style.height = (this.config.scale_height - 1) + "px";
        this.$grid_scale.style.lineHeight = lineHeigth + "px";
    }
};

gantt._render_grid_item = function (item) {
    var btn_cell_width = 20;
    if (!gantt._is_grid_visible())
        return null;

    var columns = this.getGridColumns();
    var cells = [];
    var width = 0;
    for (var i = 0; i < columns.length; i++) {
        var last = i == columns.length - 1;
        var col = columns[i];
        var cell;

        var value;
        var actions = null;
        if (col.template)
            value = col.template(item);
        else
            value = item[col.name];

        if (value.actions) {
            actions = value.actions;
            value = value.content;
        }

        if (value instanceof Date)
            value = this.templates.date_grid(value, item);

        value = "<div class='gantt_tree_content'>" + value + "</div>";
        var css = "gantt_cell" + (last ? " gantt_last_cell" : "");

        var tree = "";
        if (col.tree) {
            for (var j = 0; j < item.$level; j++)
                tree += this.templates.grid_indent(item);

            var has_child = this._has_children(item.id);
            if (has_child) {
                tree += this.templates.grid_open(item);
                tree += this.templates.grid_folder(item);
            } else {
                tree += this.templates.grid_blank(item);
                tree += this.templates.grid_file(item);
            }
        }
        var style = "width:" + (col.width - (actions ? btn_cell_width : 0) - (last ? 1 : 0)) + "px;";
        if (this.defined(col.align))
            style += "text-align:" + col.align + ";";
        cell = "<div class='" + css + "' style='" + style + "'>" + tree + value + "</div>";
        cells.push(cell);

        if (actions) {
            cells.push(actions);
        }
    }
    var css = item.$index % 2 === 0 ? "" : " odd";
    css += (item.$transparent) ? " gantt_transparent" : "";

    css += (item.$dataprocessor_class ? " " + item.$dataprocessor_class : "");

    if (this.templates.grid_row_class) {
        var css_template = this.templates.grid_row_class.call(this, item.start_date, item.end_date, item);
        if (css_template)
            css += " " + css_template;
    }

    if (this.getState().selected_task == item.id) {
        css += " gantt_selected";
    }
    var el = document.createElement("div");
    el.className = "gantt_row" + css;
    el.style.height = this.config.row_height + "px";
    el.style.lineHeight = (gantt.config.row_height) + "px";
    el.setAttribute(this.config.task_attribute, item.id);
    el.innerHTML = cells.join("");
    return el;
};

(function ($) {
    var methods,
        yiiXHR={},
        thatSettings = [];

    methods = {
        init: function (options) {
            var settings = $.extend({
                ajaxUpdate: [],
                ajaxVar: 'ajax',
                ajaxType: 'GET',
                pagerClass: 'pager',
                loadingClass: 'loading',
                filterClass: 'filters',
                // tableClass: 'items',
                // selectableRows: 1
                // updateSelector: '#id .pager a, '#id .grid thead th a',
                // beforeAjaxUpdate: function (id) {},
                // afterAjaxUpdate: function (id, data) {},
                // selectionChanged: function (id) {},
                itemsSelector: '.items',
                config: [],
            }, options || {});

            return this.each(function () {
                var eventType,
                    that = $(this),
                    id = that.attr('id'),
                    pagerSelector = '#' + id + ' .' + settings.pagerClass.replace(/\s+/g, '.') + ' a',
                    sortSelector = '#' + id + ' ' + settings.itemsSelector + ' .gantt_grid_head_cell a.sort-link',
                    inputSelector = '#' + id + ' .' + settings.filterClass + ' input, ' + '#' + id + ' .' + settings.filterClass + ' select';

                settings.updateSelector = settings.updateSelector
                    .replace('{page}', pagerSelector)
                    .replace('{sort}', sortSelector);

                settings.filterSelector = settings.filterSelector
                    .replace('{filter}', inputSelector);

                settings.gantt = $(["#",id," ",settings.itemsSelector].join("")).dhx_gantt(settings.config);
                if (settings.dataProcessorUrl) {
                    settings.dp = new gantt.dataProcessor(settings.dataProcessorUrl);
                    settings.dp.init(settings.gantt);
                }

                settings.gantt.attachEvent("onTaskOpened", function(id){
                    if (settings.onTaskOpened !== undefined) {
                        settings.onTaskOpened(id);
                    }
                });

                settings.gantt.attachEvent("onTaskClosed", function(id){
                    if (settings.onTaskClosed !== undefined) {
                        settings.onTaskClosed(id);
                    }
                });

                settings.gantt.attachEvent("onTaskSelected", function(id){
                    if (settings.onTaskSelected !== undefined) {
                        settings.onTaskSelected(id);
                    }
                });

                settings.gantt.attachEvent("onTaskDrag", function(id, mode, copy, original, e){
                    if (settings.onTaskDrag !== undefined) {
                        settings.onTaskDrag(id, mode, copy, original, e);
                    }
                });

                settings.gantt.attachEvent("onTaskClick", function(id,e){
                    return (e.which == 1 && e.target.tagName != "A" && e.target.tagName != "INPUT" && $(e.target).parents("div.btn-cell").length == 0);
                });


                thatSettings[id] = settings;

                if (settings.ajaxUpdate.length > 0) {
                    $(document).on('click.alxdDhtmlxGantt', settings.updateSelector, function () {
                        // Check to see if History.js is enabled for our Browser
                        if (settings.enableHistory && window.History.enabled) {
                            // Ajaxify this link
                            var url = $(this).attr('href').split('?'),
                                params = $.deparam.querystring('?'+ (url[1] || ''));

                            delete params[settings.ajaxVar];
                            window.History.pushState(null, document.title, decodeURIComponent($.param.querystring(url[0], params)));
                        } else {
                            $('#' + id).alxdDhtmlxGantt('update', {url: $(this).attr('href')});
                        }
                        return false;
                    });
                }

                $(document).on('change.yiiGridView keydown.yiiGridView', settings.filterSelector, function (event) {
                    if (event.type === 'keydown') {
                        if (event.keyCode !== 13) {
                            return; // only react to enter key
                        } else {
                            eventType = 'keydown';
                        }
                    } else {
                        // prevent processing for both keydown and change events
                        if (eventType === 'keydown') {
                            eventType = '';
                            return;
                        }
                    }
                    var data = $(settings.filterSelector).serialize();
                    if (settings.pageVar !== undefined) {
                        data += '&' + settings.pageVar + '=1';
                    }
                    if (settings.enableHistory && settings.ajaxUpdate !== false && window.History.enabled) {
                        // Ajaxify this link
                        var url = $('#' + id).alxdDhtmlxGantt('getUrl'),
                            params = $.deparam.querystring($.param.querystring(url, data));

                        delete params[settings.ajaxVar];
                        window.History.pushState(null, document.title, decodeURIComponent($.param.querystring(url.substr(0, url.indexOf('?')), params)));
                    } else {
                        $('#' + id).alxdDhtmlxGantt('update', {data: data});
                    }
                    return false;
                });

            });
        },

        /**
         * Returns the URL that generates the grid view content.
         * @return string the URL that generates the grid view content.
         */
        getUrl: function () {
            var sUrl = thatSettings[this.attr('id')].url;
            return sUrl || this.children('.keys').attr('title');
        },

        /**
         * Performs an AJAX-based update of the gantt view contents.
         * @param options map the AJAX request options (see jQuery.ajax API manual). By default,
         * the URL to be requested is the one that generates the current content of the grid view.
         * @return object the jQuery object
         */
        update: function (options) {
                return this.each(function () {
                    var that = $(this),
                        id = that.attr('id'),
                        settings = thatSettings[id];

                        options = $.extend({
                            type: settings.ajaxType,
                            url: that.alxdDhtmlxGantt('getUrl'),
                            success: function (data) {
                                settings.gantt.clearAll();

                                data = $('<div>', {'id': 'content-wrapper'}).append(data);
                                data.removeSrcScriptExists();
                                data.removeLinkExists();

                                $.each(settings.ajaxUpdate, function (i, el) {
                                    var updateId = '#' + el;
                                    $(updateId).replaceWith($(updateId, data));
                                });

                                $("head").append(data.find('link'));
                                $("body").append(data.find('script'));

                                if (settings.afterAjaxUpdate !== undefined) {
                                    settings.afterAjaxUpdate(id, data);
                                }
                            },
                            complete: function () {
                                yiiXHR[id] = null;
                                that.removeClass(settings.loadingClass);
                            },
                            error: function (XHR, textStatus, errorThrown) {
                                var ret, err;
                                if (XHR.readyState === 0 || XHR.status === 0) {
                                    return;
                                }
                                if (customError !== undefined) {
                                    ret = customError(XHR);
                                    if (ret !== undefined && !ret) {
                                        return;
                                    }
                                }
                                switch (textStatus) {
                                    case 'timeout':
                                        err = 'The request timed out!';
                                        break;
                                    case 'parsererror':
                                        err = 'Parser error!';
                                        break;
                                    case 'error':
                                        if (XHR.status && !/^\s*$/.test(XHR.status)) {
                                            err = 'Error ' + XHR.status;
                                        } else {
                                            err = 'Error';
                                        }
                                        if (XHR.responseText && !/^\s*$/.test(XHR.responseText)) {
                                            err = err + ': ' + XHR.responseText;
                                        }
                                        break;
                                }

                                if (settings.ajaxUpdateError !== undefined) {
                                    settings.ajaxUpdateError(XHR, textStatus, errorThrown, err);
                                } else if (err) {
                                    alert(err);
                                }
                            }
                        }, options || {});

                        if (options.type === 'GET') {
                            if (options.data !== undefined) {
                                options.url = $.param.querystring(options.url, options.data);
                                options.data = {};
                            }
                        } else {
                            if (options.data === undefined) {
                                options.data = $(settings.filterSelector).serialize();
                            }
                        }
                        if(yiiXHR[id] != null){
                            yiiXHR[id].abort();
                        }
                        //class must be added after yiiXHR.abort otherwise ajax.error will remove it
                        that.addClass(settings.loadingClass);

                        if (settings.ajaxUpdate !== false) {
                            if(settings.ajaxVar) {
                                options.url = $.param.querystring(options.url, settings.ajaxVar + '=' + id);
                            }
                            if (settings.beforeAjaxUpdate !== undefined) {
                                settings.beforeAjaxUpdate(id, options);
                            }
                            yiiXHR[id] = $.ajax(options);
                        } else {  // non-ajax mode
                            if (options.type === 'GET') {
                                window.location.href = options.url;
                            } else {  // POST mode
                                var form = $('<form action="' + options.url + '" method="post"></form>').appendTo('body');
                                if (options.data === undefined) {
                                    options.data = {};
                                }

                                if (options.data.returnUrl === undefined) {
                                    options.data.returnUrl = window.location.href;
                                }

                                $.each(options.data, function (name, value) {
                                    form.append($('<input type="hidden" name="t" value="" />').attr('name', name).val(value));
                                });
                                form.submit();
                            }
                        }
                    });
                    // $.ajax({
                    //     url:
                    //     type: "GET",
                    // });
        },
    };

    $.fn.alxdDhtmlxGantt = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.alxdDhtmlxGantt');
            return false;
        }
    };
})(jQuery);