<?php

$body = <<<EOT
    <form>
        <div class="container">
            <div style="text-align:center;margin-top:8px;">
                <h4>Toolbox BETA</h4>
                <label>Search Toolbox</label>
                <input id="search_input" style="width:74%;height:32px;"></input>
                <button type="button" class="toolbox-btn" onclick='loadMore(1, $("#search_input").val())' style="width:24%;height:32px;">Search</button>
            </div>
            <div style="text-align:center;">
                <select onchange="setType(this.value)" style="width:98.6%;margin-top:6px;height:32px;">
                    <option value="0">My Models</option>
                    <option value="1">Free Models</option>
                </select>
            </div>
        </div>
    </form>
    <hr>
    <div class="container">
        <div class="toolbox-container">
            <ul id="item_container">
    
            </ul>
        </div>
    </div>
    <hr>
    <div class="container text-center">
        <button class="toolbox-btn" id="load_button">Load More</button>
    </div>
    <script>

    /*
        Finobe Ghetto Toolbox 2021
        Luckily MOST of jquery works with qt 4

        0 = Current User Models
        1 = Public User Models
    */

    var category = "";
    var api = "https://www.idk16.xyz/studio/ide/toolbox/items";
    
    function insertAsset(assetid)
    {
        window.external.Insert("https://www.idk16.xyz/asset/?id="+assetid);
    }

    function setType(type)
    {
        type = parseInt(type)
        switch(type)
        {
            case 0: //user models
                category = "UserModels";
                break;
            case 1: //public user models
               category = "FreeModels";
               break;
            default:
                alert("Error Occurred");
                break;
        }
        loadMore(1);
    }

    function loadMore(page, keyword)
    {
        if (keyword === undefined) //HACK since old js sucks
        {
            keyword = "";
        }
        
        var html = '<li>';
        html += '<div class="studio-tooblox-card text-center" style="cursor: pointer;" onclick="insertAsset({id})">';
        html += '<a>';
        html += '<div class="studio-tooblox-card-img">';
        html += '<img class="img-fluid" style="width: 124px;height: 69px" src="{thumbnail}">';
        html += '</div>';
        html += '<p class="no-overflow">{name}</p>';
        html += '</a>';
        html += '</div>';
        html += '</li>';

        toolboxPageHelper("loadMore", api, "#item_container", "#load_button", html, page, 6, keyword, "No Models", "&category="+category)
    }
 
    function toolboxPageHelper(callName, api, container, buttonid, html, page, limit, keyword, message, args)
    {
        getJSONCDS(api + '?limit=' + limit + '&page=' + page + '&keyword=' + keyword + args)
        .done(function(jsonData) {
            var showButton = false;
            var pageCount = jsonData.pageCount;
            var pageResults = jsonData.pageResults;
            var currentPage = page;
            var nextPage = currentPage + 1;
            var previousPage = currentPage - 1;
            var previousHtml = "";

            $(buttonid).hide();

            if (nextPage > pageCount) {
                nextPage = pageCount;
            }
                    
            if (previousPage == 0) {
                previousPage = 1; 
            }
                            
            if (pageCount > 1) {
                showButton = true;
            }
                    
            if (showButton)
            {
                $(buttonid).show();
                if (currentPage == pageCount)
                {
                    $(buttonid).hide();
                }

                if (currentPage > 1)
                {
                    previousHtml = $(container).html();
                }

                $(buttonid).attr("onclick",callName + "(" + nextPage + ")");
            }
            
            $(container).html(previousHtml + parseHtml(html, pageResults, jsonData, message));
            $("html, body").animate({ scrollTop: $(document).height() }, "fast");
        });
    }

    setType(0); //default my models
    console.log("Toolbox Initialized");

    </script>
EOT;

pageHandler();
$ph->navbar = "";
$ph->footer = "";
$ph->studio = true; //force default theme (light)
$ph->pageTitle("Toolbox");
$ph->body = $body;
$ph->output();