/*
	Alphaland JS Utilities 2021
*/

//utility for populating HTML with data from object OR calling a function with the objects current position and replacing the marker with returned data
//[] for calling a function and replacing the marker with the return data (allows for more logic) (turned out to be incredibly useful)
//{} for replacing the marker with the objects data
//TODO: clean up?
function parseHtml(html, limit, object, message, singleObject=false) {
	var html_result = '';
	var buffer = "";
	
	for (var i = 0; i < limit; i++) {
		if (singleObject) {
			var objectData = object;
		} else {
			var objectData = object[i];
		}

		buffer = html;
		
		//we have reached the end of the data available
		if (objectData === undefined) {
			break;
		}
		
		for (var pos = 0; pos <= html.length; pos++) {
			var firstFound = false;
			var functionCall = false;
			var secondPositionIdentifier = "";
			if (html.charAt(pos) == "{") {
				firstFound = true;
				secondPositionIdentifier = "}";
			} else if (html.charAt(pos) == "[") {
				firstFound = true;
				functionCall = true;
				secondPositionIdentifier = "]";
			}

			if (firstFound) { //first position
				for (var len = pos; len; len++) {
					if (html.charAt(len) == secondPositionIdentifier) { //second position
						var marker = "";
						for (var d = pos; d < len+1; d++) { //data between the two positions
							marker += html.charAt(d);
						}
						
						//where we handle the data O_O
						//using replace instead of replaceAll for compatibility
						if (!functionCall) {
							buffer = buffer.replace(marker, objectData[marker.substring(1, marker.length - 1)]); //replace marker with data from the object
						} else {
							try {
								buffer = buffer.replace(marker, window[marker.substring(1, marker.length - 1)](objectData)); //replace the marker with the data returned from the call
							}
							catch (error) {
								console.log('[functionCall] Something went wrong.')
							}
						}
						break;
					}
				}
			}
		}
		html_result += buffer;
	}
	if (html_result == "") {
		html_result = message;
	}
	return html_result;
}

//http get json no cds
function getJSON(url) {
	return $.getJSON(url);
}

//http get json cds
function getJSONCDS(url) {
	return $.ajax(url, {
		xhrFields: {
			withCredentials: true
		},
		crossDomain: true
	});
}

//http post json cds
function postJSONCDS(url, jsondata) {
	return $.ajax({
		type: 'POST',
		url: url,
		xhrFields: {
			withCredentials: true
		},
		crossDomain: true,
		data: jsondata,
		dataType: 'json',
	});
}

//pretty ghetto but is incredibly useful for multi page helper, ex output: ,1,"ass",2
function parseArrayArgs(args) {
	var parsed = ",";
	for (const arg of args) {
		if (typeof arg == "string") {
			parsed += "'"+arg+"',";
		} else {
			parsed += arg+",";
		}
	}
	return parsed.substring(0, parsed.length - 1); //remove last comma
}

//utility is for making pages easier
function staticPageHelper(api, loadingurl, container, html, page, limit, keyword, message, optionalArgs = "") {
	if (loadingurl !== "") {
		var loadingHtml = '<div class="text-center">';
		loadingHtml += '<img src="' + loadingurl + '" class="loading-rotate" width="250" height="250" />';
		loadingHtml += '</div>';
		
		$(container).html(loadingHtml);
	}

	getJSONCDS(api + '?limit=' + limit + '&page=' + page + '&keyword=' + keyword + optionalArgs)
	.done(function(jsonData) {
		$(container).html(parseHtml(html, limit, jsonData, message));
	});
}

//utility for making pagination enabled pages easy
function multiPageHelper(callName, args, api, loadingurl, container, buttonsid, html, page, limit, keyword, message, optionalArgs = "") {
	if (loadingurl !== "") {
		var loadingHtml = '<div class="text-center">';
		loadingHtml += '<img src="' + loadingurl + '" class="loading-rotate" width="250" height="250" />';
		loadingHtml += '</div>';
		
		$(container).html(loadingHtml);
	}

	var parsedArguments = "";
	if (args && args.constructor === Array) {
		parsedArguments = parseArrayArgs(args)
	}
	
	$(buttonsid).html('');
	
	getJSONCDS(api + '?limit=' + limit + '&page=' + page + '&keyword=' + keyword + optionalArgs)
	.done(function(jsonData) {
		var showButtons = false;
		var buttons = '';
		var firstPos = 0;
		var secondPos = 0;
		var pageCount = jsonData.pageCount;
		var pageResults = jsonData.pageResults;
		var currentPage = page;
		var nextPage = currentPage + 1;
		var previousPage = currentPage - 1;
		var pageButtonEnd = currentPage + 3;

		if (nextPage > pageCount) {
			nextPage = pageCount;
		}
				
		if (previousPage == 0) {
			previousPage = 1; 
		}
						
		if (pageCount > 1) {
			showButtons = true;
		}	
				
		if (showButtons)
		{
			if (pageCount < 5) {
				firstPos = 1;
				secondPos = pageCount;
			}
			else 
			{
				if (previousPage == currentPage - 1) {
					if (currentPage >= pageCount - 2) {
						firstPos = pageCount - 3;
					} else {
						firstPos = currentPage-1;
					}	
					secondPos = pageButtonEnd-1;	
				} else { 
					firstPos = currentPage;
					secondPos = pageButtonEnd;
				}
			}

			buttons+= '<button type="button" onclick="' + callName + '(1' + parsedArguments + ')" class="btn btn-danger">«</button>';
			buttons+= '<button type="button" onclick="' + callName + '(' + previousPage + parsedArguments + ')" class="btn btn-danger">‹</button>';
					
			for (i = firstPos; i <= secondPos; i++) {
				if (i <= pageCount) {
					buttons+= '<button type="button" onclick="' + callName + '(' + i + parsedArguments + ')" ' + ((i == currentPage) ? 'style="background-color:  #c82333;"' : '') + 'class="btn btn-danger">' + i + '</button>';
				}
			}
					
			buttons+= '<button type="button" onclick="' + callName + '(' + nextPage + parsedArguments + ')" class="btn btn-danger">›</button>';
			buttons+= '<button type="button" onclick="' + callName + '(' + pageCount + parsedArguments + ')" class="btn btn-danger">»</button>';
		}

		$(container).html(parseHtml(html, pageResults, jsonData, message));
		$(buttonsid).html(buttons);
	});
}

//comments
class Comments {
	constructor(assetid, commentsid, commentscontainer, buttonscontainer, successid, errorid, inputid, messageDelay, newObject) {
		this.assetid = assetid;
		this.commentsid = commentsid;
		this.commentscontainer = commentscontainer;
		this.buttonscontainer = buttonscontainer;
		this.successid = successid;
		this.errorid = errorid;
		this.inputid = inputid;
		this.messageDelay = messageDelay;
		this.newObject = newObject;
		
		var gameInfo = "https://api.alphaland.cc/game/info?id=" + this.assetid;
		var self = this;
		getJSONCDS(gameInfo)
		.done(function(jsonData) {
			if (jsonData.CommentsEnabled) {
				$(self.commentsid).show();
				self.commentsPage();
			}
		});
	}
	commentsPage(page=1)
	{
		var html = '<div class="row mb-2">';
		html += '<div class="card w-100">';
		html += '<div class="card-body">';
		html += '<h6>Comment by <a class="red-a" href="/profile/view?id={userid}">{username}</a> : <a style="color:grey;">{date}</a></h6>';
		html += '<div class="row marg-bot-15">';
		html += '<div class="col-sm-1">';
		html += '<a href="/profile/view?id={userid}"><img class="card-img-top marg-bot-15" src="{thumbnail}" style="width:4rem;border-radius:100%;"></a>';
		html += '</div>';
		html += '<div class="col-sm" style="overflow:hidden;">';
		html += '<p>{comment}</p>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		html += '</div>';
		
		multiPageHelper(this.newObject + ".commentsPage", [], "https://api.alphaland.cc/comments/", "https://api.alphaland.cc/logo", this.commentscontainer, this.buttonscontainer, html, page, 10, "", "No comments", "&assetId=" + this.assetid);
	}
	submitComment(comment) {
		var self = this;
		postJSONCDS("https://api.alphaland.cc/comments/newcomment?assetId=" + this.assetid, JSON.stringify({"comment": comment}))
		.done(function(object) {
			var alert = object.alert;
			var messageid = self.errorid;
			if (alert == "Comment Placed") {
				messageid = self.successid;
				$(self.inputid).val('');
				self.commentsPage();
			}
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() {
				$(messageid).hide();
			}, self.messageDelay);
		});
	}
}