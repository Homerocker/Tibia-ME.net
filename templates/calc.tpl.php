<div class="text-center">
    <button type="button" class="button" id="button_war"><?= _('Warrior') ?></button>
    <button type="button" class="button secondary" id="button_wiz" onclick="set_vocation(this);"><?= _('Wizard') ?></button>
</div>
<table class="unstriped">
    <tr>
        <td class="shrink"><a id="ignore_head" onclick="ignore_item('head')" title="<?= _('Remove from comparison') ?>">&times;</a></td>
        <td id="head_stats"></td>
        <td><img id="icon_head" src="" alt=""/></td>
        <td><img id="icon_amulet" src="" alt=""/></td>
        <td id="amulet_stats"></td>
        <td><a id="ignore_amulet" onclick="ignore_item('amulet')" title="<?= _('Remove from comparison') ?>">&times;</a></td>
    </tr>
    <tr>
        <td><a id="ignore_shield" onclick="ignore_item('shield')" title="<?= _('Remove from comparison') ?>">&times;</a></td>
        <td id="shield_stats"></td>
        <td><img id="icon_shield" src="" alt=""/></td>
        <td><img id="icon_torso" src="" alt=""/></td>
        <td id="torso_stats"></td>
        <td><a id="ignore_torso" onclick="ignore_item('torso')" title="<?= _('Remove from comparison') ?>">&times;</a></td>
    </tr>
    <tr>
        <td><a id="ignore_legs" onclick="ignore_item('legs')" title="<?= _('Remove from comparison') ?>">&times;</a></td>
        <td id="legs_stats"></td>
        <td><img id="icon_legs" src="" alt=""/></td>
        <td><img id="icon_ring" src="" alt=""/></td>
        <td id="ring_stats"></td>
        <td><a id="ignore_ring" onclick="ignore_item('ring')" title="<?= _('Remove from comparison') ?>">&times;</a></td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <td class="text-center"><img src="/images/icons/hit.png" alt="<?= _('hit') ?>" title="<?= _('hit') ?>"/></td>
            <td class="text-center"><img src="/images/icons/fire.png" alt="<?= _('fire') ?>" title="<?= _('fire') ?>"/></td>
            <td class="text-center"><img src="/images/icons/ice.png" alt="<?= _('ice') ?>" title="<?= _('ice') ?>"/></td>
            <td class="text-center"><img src="/images/icons/energy.png" alt="<?= _('energy') ?>" title="<?= _('energy') ?>"/></td>
            <td class="text-center"><img src="/images/icons/soul.png" alt="<?= _('soul') ?>" title="<?= _('soul') ?>"/></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center" id="stat_hit">0%</td>
            <td class="text-center" id="stat_fire">0%</td>
            <td class="text-center" id="stat_ice">0%</td>
            <td class="text-center" id="stat_energy">0%</td>
            <td class="text-center" id="stat_soul">0%</td>
        </tr>
    </tbody>
</table>

<div class="grid-x grid-padding-x grid-padding-y">
    <div class="cell medium-6 large-4">
        <h3><?= _('Automatic selection') ?></h3>
        <div class="callout primary">
            <?
            foreach (array('hit', 'fire', 'ice', 'energy', 'soul') as $element):
                ?>
                <input id="cbox_<?= $element ?>" type="checkbox" name="cbox_<?= $element ?>"<?=
                ($element === 'hit' ? ' checked' : '')
                ?>/>
                <label for="cbox_<?= $element ?>"><?= _($element) ?></label><br/>
            <? endforeach; ?>
            <input type="checkbox" id="incl_upgraded"/>
            <label for="incl_upgraded"><?= _('Include upgraded items') ?></label>
            <label for="def_level"><?= _('Defense level') ?></label>
            <input id="def_level" type="text" maxlength="3" accept="*N"/>
            <label for="evenly"><?= _('Stats distribution') ?></label>
            <select id="evenly">
                <option value="0"><?= _('cumulatively') ?></option>
                <option value="1"><?= _('evenly') ?></option>
            </select>
            <button class="button primary" type="button" onclick="get_bis_set();"><?= _('Calculate') ?></button>
        </div>
    </div>

    <div class="cell medium-6 large-4">
        <h3><?= _('Manual selection') ?></h3>
        <div class="callout primary">
            <?php
            foreach (array('head', 'amulet', 'torso', 'shield', 'legs', 'ring') as
                        $slot) {
                echo '<div class="ym-fbox ym-fbox-select">';
                echo '<label for="' . $slot . '">' . _(ucfirst($slot)) . '</label>';
                echo '<select id="', $slot, '" onchange="set_item(this);">';
                echo '<option></option>';
                echo '</select>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <div class="cell medium-6 large-4">
        <h3><?= _('Ignored items') ?></h3>
        <div class="callout primary">
            <ul id="ignored_items"></ul>
        </div>
    </div>
</div>



<script type="text/javascript">
    //<![CDATA[
    var stat_priority = ["hit"];
    var incl_upgraded = 0;
    var vocation = "warrior";
    var def_level;
    var evenly = 0;
    var set;
    var manual = false;
    document.addEventListener('DOMContentLoaded', function () {
        get_bis_set();
    });
    function get_bis_set() {
        def_level = document.getElementById("def_level").value;
        stat_priority = [];
        $.each(['hit', 'fire', 'ice', 'energy', 'soul'], function (id, element) {
            if (document.getElementById("cbox_" + element).checked) {
                stat_priority.push(element);
            }
        });
        var select = document.getElementById("evenly");
        incl_upgraded = document.getElementById("incl_upgraded").checked ? 1 : 0;
        evenly = select.options[select.selectedIndex].value;
        manual = false;
        $.ajax(ajaxSettings({
            set: JSON.stringify(set),
            vocation: vocation,
            def_level: def_level,
            stat_priority: stat_priority,
            incl_upgraded: incl_upgraded,
            evenly: evenly
        })).done(function (response) {
            set = $.parseJSON(response);
            def_level = set.def_level;
            document.getElementById("def_level").value = def_level;
            redrawUI();
        });
    }
    function raise(stat_raise) {
        $.ajax(ajaxSettings({
            set: JSON.stringify(set),
            stat_raise: stat_raise,
            vocation: vocation,
            def_level: def_level,
            stat_priority: stat_priority
        })).done(function (response) {
            set = $.parseJSON(response);
            redrawUI();
        });
    }
    function get_stats() {
        var stats = {
            hit: 0,
            fire: 0,
            ice: 0,
            energy: 0,
            soul: 0
        };
        $.each(['head', 'shield', 'legs', 'amulet', 'torso', 'ring'], function (k, slot) {
            if (set[slot] === null) {
                return true;
            }
            $.each(stats, function (k, v) {
                stats[k] = def_calc(v, set[slot][k]);
            });
        });
        return stats;
    }
    function def_calc(base, add) {
        return base + Math.ceil(add - base * add / 100);
    }
    function set_vocation(elem) {
        vocation = elem.id === "button_war" ? "warrior" : "wizard";
        get_bis_set();
        $(elem).removeClass("secondary").removeAttr('onclick', '').unbind('click');
        (vocation === "warrior" ? $("#button_wiz") : $("#button_war"))
                .addClass("secondary").click(function () {
            set_vocation(this);
        });
    }
    function set_item(select) {
        $.ajax(ajaxSettings({
            set: JSON.stringify(set),
            item_id: select.options[select.selectedIndex].value
        })).done(function (response) {
            manual = true;
            set = $.parseJSON(response);
            redrawUI();
        });
    }
    function ignore_item(slot) {
        if (set[slot] === null) {
            return false;
        }
        $.ajax(ajaxSettings({
            set: JSON.stringify(set),
            ignore: set[slot].id,
            vocation: vocation,
            def_level: def_level,
            stat_priority: stat_priority,
            incl_upgraded: incl_upgraded,
            evenly: evenly
        })).done(function (response) {
            set = $.parseJSON(response);
            redrawUI();
        });
    }
    function unignore_item(item_id) {
        $.ajax(ajaxSettings({
            set: JSON.stringify(set),
            unignore: item_id,
            vocation: vocation,
            def_level: def_level,
            stat_priority: stat_priority,
            incl_upgraded: incl_upgraded,
            evenly: evenly
        })).done(function (response) {
            set = $.parseJSON(response);
            redrawUI();
        });
    }
    function redrawUI() {
        var stats = get_stats();
        $.each(stats, function (k, v) {
            document.getElementById("stat_" + k).innerHTML = v + "%";
        });
        $.each(['head', 'shield', 'legs', 'amulet', 'torso', 'ring'], function (k, slot) {
            var elem = document.getElementById(slot + "_stats");
            elem.innerHTML = '';
            if (set[slot] === null) {
                return true;
            }
            $.each(elements, function (k, element) {
                if (set[slot][element] === "0") {
                    return; // continue
                }
                for (var i = 0; i < k; i++) {
                    if (set[slot][element] === set[slot][elements[i]]) {
                        return; // element already grouped, continue
                    }
                }
                for (var i = k; i < elements.length; i++) {
                    if (i !== k && set[slot][element] !== set[slot][elements[i]]) {
                        continue;
                    }
                    var img = document.createElement("img");
                    img.setAttribute("src", "/images/icons/" + elements[i] + ".png");
                    if (elem.innerHTML !== "" && elem.innerHTML.slice(-1) !== ">") {
                        elem.innerHTML += "&nbsp;";
                    }
                    elem.appendChild(img);
                }
                elem.innerHTML += set[slot][element];
            });
        });
        $.each(['head', 'shield', 'legs', 'amulet', 'torso', 'ring'], function (k, slot) {
            var icon = $("#icon_" + slot);
            icon.attr("src", (set[slot] === null || set[slot].icon === null) ? '/images/item_no_icon.png' : '<?= ICONS_DIR ?>' + set[slot].icon);
            icon.attr("title", set[slot] === null ? '' : set[slot].name);
        });
        $.each(['hit', 'fire', 'ice', 'energy', 'soul'], function (id, stat) {
            if (stat_priority.indexOf(stat) === -1 || stat_priority.length < 2 || manual === true) {
                if (document.body.contains(document.getElementById("raise_" + stat))) {
                    document.getElementById("stat_" + stat).innerHTML = document.getElementById("raise_" + stat).innerHTML.slice(0, -1);
                }
            } else if (!document.body.contains(document.getElementById("raise_" + stat)) && stat_priority.length > 1) {
                var a = document.createElement("a");
                a.setAttribute("onclick", "raise('" + stat + "')");
                a.setAttribute("id", "raise_" + stat);
                a.classList.add("pointer");
                a.innerHTML = document.getElementById("stat_" + stat).innerHTML + "&uarr;";
                document.getElementById("stat_" + stat).innerHTML = "";
                document.getElementById("stat_" + stat).appendChild(a);
            }
        });
        // rebuilding armors list
        $.each(<?= json_encode($items) ?>, function (vocation, slot_set) {
            if (vocation !== window.vocation && vocation !== "null") {
                return true;
            }
            $.each(slot_set, function (slot, items) {
                $('#' + slot).empty();
                $.each(items, function (id, name) {
                    var option = document.createElement("option");
                    option.value = id;
                    option.innerHTML = name;
                    if (set[slot] !== null && id === set[slot].id) {
                        option.selected = true;
                    }
                    document.getElementById(slot).appendChild(option);
                });
            });
        });
        // updating list of ignored items
        var ignored_items = document.getElementById("ignored_items");
        if (Object.keys(set.ignored_items).length === 0) {
            ignored_items.innerHTML = "<?= _('none') ?>";
        } else {
            ignored_items.innerHTML = "";
            $.each(set.ignored_items, function (k, item) {
                var li = document.createElement('li');
                var a = document.createElement('a');
                a.onclick = function () {
                    unignore_item(item.id);
                };
                a.innerHTML = item.name + "&nbsp;&times;";
                li.appendChild(a);
                ignored_items.appendChild(li);
            });
        }
        $.unblockUI();
    }
    function ajaxSettings(send_data) {
        return {
            type: "POST",
            url: "/gamecontent/calc/api.php",
            data: send_data,
            beforeSend: function () {
                $.blockUI({
                    message: "<?= _('Calculating...') ?>",
                    css: {
                        textAlign: "center",
                        cursor: "wait",
                        fontSize: "large",
                        width: "100%",
                        left: 0,
                        right: 0,
                        margin: "auto"
                    }
                });
            }
        };
    }
    //]]>
</script>
