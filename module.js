M.tool_coursesearch = {

        init: function (Y) {

            Y.one('#id_solr-btn-ping').once('click', function (e) {

                var ajax_url = 'ajaxcalls.php?action=ping' + '&sesskey=' + M.cfg.sesskey + '&host=' + Y.one('#id_solrhost').get('value') + '&port=' + Y.one('#id_solrport').get('value') + '&path=' + Y.one('#id_solrpath').get('value');

                Y.one('#id_solr-btn-ping').insert('<span id=solr-ping-status>&nbsp;<img src="pix/ajax-circle.gif"></span>', 'after');

                Y.io(ajax_url, {
                    on: {
                        success: function (id, data) {
                            try {
                                var resp = Y.JSON.parse(data.responseText);
                            } catch (e) {
                                alert(e);
                                return;
                            }

                            if (resp.status == 'ok') {
                                Y.one('#solr-ping-status').setHTML('&nbsp;<img src="pix/success.png">');
                                setTimeout("M.tool_coursesearch.clearSaveStatus('#solr-ping-status')", 2000);
                            } else {
                                Y.one('#solr-ping-status').setHTML('&nbsp;<img src="pix/warning.png">');
                                setTimeout("M.tool_coursesearch.clearSaveStatus('#solr-ping-status')", 2000);

                            }
                        }

                    }
                });

                return false;
            });
        },
        clearSaveStatus: function (id) {
            Y.one(id).setHTML('');
            Y.use(M.tool_coursesearch.init);
        },
        loadcontent: function (Y) {
            Y.one('#id_solr-btn-loadcontent').once('click', function (e) {
                Y.one('#id_solr-btn-loadcontent').insert("<span id='solr-loadcontent-status'>&nbsp;<img src='pix/ajax-circle.gif'></span>", 'after');
                M.tool_coursesearch.doIndex(1);
            });
        },
        doIndex: function (prev) {
            Y.io('ajaxcalls.php?action=index' + '&sesskey=' + M.cfg.sesskey, {
                method: "GET",
                data: "prev=" + prev,
                on: {
                    success: function (o, res) {
                        M.tool_coursesearch.doIndexHandleResults(Y.JSON.parse(res.responseText));
                    }
                }
            });
        },
        doIndexHandleResults: function (data) {
            Y.one('#solr-loadcontent-status').setHTML('&nbsp;<img src="pix/ajax-circle.gif"> ' + data.percent + '%');
            if (!data.end) {
                M.tool_coursesearch.doIndex(data.last);
            } else {
                Y.one('#solr-loadcontent-status').setHTML('&nbsp;<img src="pix/success.png">');
                setTimeout("M.tool_coursesearch.clearSaveStatus('#solr-loadcontent-status')", 1000);
            }
        },
        deleteAll: function () {
            Y.one('#id_solr-btn-deleteall').on('click', function (e) {
                Y.one('#id_solr-btn-deleteall').insert('<span id=solr-deleteall-status>&nbsp;<img src="pix/ajax-circle.gif"></span>', 'after');
                Y.io('ajaxcalls.php?action=deleteall' + '&sesskey=' + M.cfg.sesskey, {
                    method: "GET",
                    on: {
                        success: function (o, res) {
                            var resp = Y.JSON.parse(res.responseText);
                            if (resp.status == 'ok') {
                                Y.one('#solr-deleteall-status').setHTML('&nbsp;<img src="pix/success.png">');
                                setTimeout("M.tool_coursesearch.clearSaveStatus('#solr-deleteall-status')", 2000);
                            } else {
                                Y.one('#solr-deleteall-status').setHTML('&nbsp;<img src="pix/warning.png">&nbsp' + resp.message);
                                setTimeout("M.tool_coursesearch.clearSaveStatus('#solr-deleteall-status')", 2000);
                            }
                        }
                    }
                });
            });
        },
        optimize: function () {
            Y.one('#id_solr-btn-optimize').once('click', function (e) {
                Y.one('#id_solr-btn-optimize').insert('<span id=solr-optimize-status>&nbsp;<img src="pix/ajax-circle.gif"></span>', 'after');
                Y.io('ajaxcalls.php?action=optimize' + '&sesskey=' + M.cfg.sesskey, {
                    method: "GET",
                    on: {
                        success: function (o, res) {
                            var resp = Y.JSON.parse(res.responseText);
                            if (resp.status == 'ok') {
                                Y.one('#solr-optimize-status').setHTML('&nbsp;<img src="pix/success.png">');
                                setTimeout("M.tool_coursesearch.clearSaveStatus('#solr-optimize-status')", 2000);
                            } else {
                                Y.one('#solr-optimize-status').setHTML('&nbsp;<img src="pix/warning.png">&nbsp' + resp.message);
                                setTimeout("M.tool_coursesearch.clearSaveStatus('#solr-optimize-status')", 2000);
                            }
                        }
                    }
                });
            });
        }
    }