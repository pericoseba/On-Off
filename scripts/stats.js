/**
 * Gets the statistics page. On this page, the user can choose the host or day he wants to see statistics.
 * Calls the action getHostsNamesAndID
 * Uses the template "statsBeginPage.html"
 * @function
 */
function seeAllStats() {
	deletePeriodicGetStatus();
	$.ajax({
		url: "?ajax=true&action=getHostsNamesAndID",
		type: 'post',
		success: function(data){	
			var saveData = data;	
			$.get('templates/statsBeginPage.html', function(data) {
				$('#content').html( $.tmpl(data, saveData) );
				//$('#content').append("<div id='statsDiv'></div>"); //Remove this sentence, I create it manually.
				var $ifAllHosts = $("#ifAllHosts");
				var $ifCustomHost = $("#ifCustomHost");
								
				$("#dateHostsStats").datepicker();
				$("#chooseStatsType").buttonset();
				$("#ifAllHosts").hide();
				
				$("#customHostStats").click( function (){
					$ifAllHosts.hide();	
					$ifCustomHost.show();				
				});
				
				$("#allHostsStats").click( function (){
					$ifAllHosts.show();
					$ifCustomHost.hide();								
				});
				
				$("#viewStats").button().click( function () {
					customHostStats = document.getElementById("customHostStats");
					allHostsStats = document.getElementById("allHostsStats");
					var dateHostsStats = $("input#dateHostsStats").val();
					var selectedHost = $("select#allHosts").val();
															
					if (customHostStats.checked) {
						getCustomStats(selectedHost);
					} else if (allHostsStats.checked) {
						if (dateHostsStats != '') {
							var day = dateHostsStats.substring(3,5);
							var month = dateHostsStats.substring(0,2);
							var year = dateHostsStats.substring(6,10);
							seeAllHostsStats(day, month, year);
						} else {
							return false;
						}
					}
					
					return false;		
				});
			});
		},
		dataType: 'json'		
	});
			
	return false;
}

/**
 * Gets the statistics for an host, and prints them for the user.
 * Calls the action getCustomStats
 * Uses the template "statsCustomHostAllDays.html"
 * @function
 * @param {Number} idHost The id of the host we want stats
 */
function getCustomStats(idHost) {
	deletePeriodicGetStatus();
	$.ajax({
		url: "?ajax=true&action=getCustomStats",
		type: 'post',
		data: "&idHost=" + idHost,
		success: function(data){
			if (data===false) {
				$('#statsDiv').html("Sorry, this host has no registered stats")
			} else {
				var saveData = data;	
				$.get('templates/statsCustomHostAllDays.html', function(data) {
					$('#statsDiv').html( $.tmpl(data, saveData) );		
				});
			}
		},
		dataType: 'json'		
	});
			
	return false;
}

/**
 * Gets the statistics for a day, and prints them for the user.
 * Calls the action getDailyStats
 * Uses the template "statsCustomDayAllHosts.html"
 * @function
 * @param {Number} day the day (01..31)
 * @param {Number} month the month (01..12)
 * @param {Number} year the year (2000..2???)
 */
function seeAllHostsStats(day, month, year) {
	deletePeriodicGetStatus();
	$.ajax({
		url: "?ajax=true&action=getDailyStats",
		type: 'post',
		data: "&day=" + day + "&month=" + month + "&year=" + year,
		success: function(data){
			if (data===false) {
				$('#statsDiv').html("Sorry, this host has no registered stats")
			} else {	
				var saveData = data;	
				$.get('templates/statsCustomDayAllHosts.html', function(data) {
					$('#statsDiv').html( $.tmpl(data, saveData) );
				});
			}
		},
		dataType: 'json'		
	});
			
	return false;
}