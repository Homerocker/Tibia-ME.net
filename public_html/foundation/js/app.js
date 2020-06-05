$(document).foundation();

function viewscores_graph_draw(data) {
    var dataset = [];
    var color;
    for (var i = 0; i < data.length; ++i) {
        if (data[i] === '0') {
            dataset.push({data: [[i, null]]});
        } else {
            color = data[i] > 0 ? 'rgb(0, 128, 0)' : 'rgb(255, 0, 0)';
            dataset.push({data: [[i, data[i]]], color: color});
        }
    }
    $.plot('#viewscores_graph', dataset, {
        yaxis: {
            tickFormatter: function (val) {
                var length = val.toString().length;
                if (length > 4) {
                    val = val / 1000;
                }
                if (val > 0) {
                    val = '+' + val;
                }
                return length > 4 ? val + 'k' : val;
            }
        },
        xaxis: {
            ticks: false
        },
        bars: {
            show: true,
            barWidth: 0.5,
            fill: 1
        },
        grid: {
            borderWidth: 0
        }
    });
}

function unique_id() {
    do {
        var id = "uniqid_" + Math.floor((Math.random() * 100000) + 1);
    } while (document.getElementById(id));
    return id;
}

var elements = ['hit', 'fire', 'ice', 'energy', 'soul'];

function toggle() {
    for (var i = 0; i < arguments.length; ++i) {
        if (typeof arguments[i] === "object") {
            $(arguments[i].nextSibling).toggle('fast');
            $(arguments[i]).toggle('fast');
            continue;
        }
        var id = arguments[i];
        $('#' + id).toggle('fast');
        $('.' + id).toggle('fast');
        var expander = $("#" + id + "-expander");
        if (expander.length === 0) {
            continue;
        }
        expander.text(expander.text() === '-' ? '+' : '-');
    }
}

function like_toggle(target_type, target_id, like) {
    $.ajax({
        cache: false,
        url: "/api.php",
        data: {
            'call': 'like_toggle',
            'target_type': target_type,
            'target_id': target_id,
            'like': like
        }
    }).done(function (data) {
        data = JSON.parse(data);
        if (data['result'] !== false) {
            var up = parseInt(data['result']['up']);
            var down = parseInt(data['result']['down']);
            $("#rating" + target_id + "up").text(up);
            $("#rating" + target_id + "down").text(down);
            if (data['result']['like'] === "1") {
                $("#like" + target_id).addClass("like_voted");
            } else {
                $("#like" + target_id).removeClass("like_voted");
            }
            if (data['result']['like'] === "0") {
                $("#dislike" + target_id).addClass("like_voted");
            } else {
                $("#dislike" + target_id).removeClass("like_voted");
            }
            var p;
            if (up + down === 0) {
                p = -1;
            } else if (up === 0 && down > 0) {
                p = 0;
            } else {
                p = up * (100 / (down + up));
            }
            $("#rating" + target_id).attr("src", "/images/rating.php?p=" + p.toString());
        }
    });
}

function chat_send(input_id, nickname, world) {
    var message = $.trim($("#" + input_id).val());
    if (!message) {
        return;
    }
    $("#" + input_id).val("");
    $("#chat").append('<p class="chat-sending"><span class="text-small b">' + nickname + (!world ? '' : ',&nbsp;w' + world) + '</span> <span class="text-small">' + chat_date() + '</span><br/>' + message + '</p>');
    chat_scroll();
    $.ajax({
        method: "GET",
        url: "/api.php",
        data: {
            call: "chat_send_message",
            message: message
        }
    }).done(function () {
        chat_update();
    });
}

var chat_updater;

function chat_update() {
    var last_id = chat_get_last_id();
    var chat = $("#chat");
    var chat_autoscroll = (Math.abs(chat[0].scrollHeight - chat.scrollTop() - chat.innerHeight()) < 1);
    $.get('/api.php?call=chat_update&last_id=' + last_id, function (data) {
        data = $.parseJSON(data)['result'];
        $.each(data, function (k, v) {
            chat.append('<p id="chat' + v.id + '"><span class="text-small b">' + v.nickname + (!v.world ? '' : ',&nbsp;w' + v.world) + '</span> <span class="text-small">' + chat_date(v.timestamp * 1000) + '</span><br/>' + v.message + '</p>');
        });
        if (last_id !== 0 && data.length !== 0) {
            var audio = new Audio('/notification.mp3');
            audio.play();
        }
        if (typeof chat_updater !== 'undefined') {
            clearTimeout(chat_updater);
        }
        chat_updater = setTimeout(chat_update, 5000);
    }).done(function () {
        $(".chat-sending").remove();
        if (chat_autoscroll) {
            chat_scroll();
        }
    });
}

function chat_scroll() {
    var chat = document.getElementById("chat");
    chat.scrollTop = chat.scrollHeight;
}

function chat_get_last_id() {
    var children = document.getElementById("chat").children;
    var id;
    for (var i = children.length - 1; i >= 0; i--) {
        id = children[i].id;
        if (typeof id !== 'undefined') {
            break;
        }
    }
    return (typeof id === 'undefined') ? 0 : id.substring(4);
}

function pad(str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}

function chat_date(timestamp) {
    var date = new Date(timestamp);
    return pad(date.getDate(), 2) + '.' + pad(date.getMonth() + 1, 2) + '.' + date.getFullYear() + ' ' + pad(date.getHours(), 2) + ':' + pad(date.getMinutes(), 2) + ':' + pad(date.getSeconds(), 2);
}

function get_platinum_bundle(required_amount, currency) {
    $.get('/api.php?call=get_platinum_bundle&required_amount=' + required_amount + '&currency=' + currency, function (data) {
        data = $.parseJSON(data)['result'];
        $("#amount_display").text(data.amount);
        $("#amount").val(data.amount);
        $("#price").text(data.price);
    }).done(function () {
        
    });
}