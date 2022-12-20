"use strict";

/**
* The Arr widget on the dashboard.
*
* @class Arr
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.arr = function (options) {
        /**
        * All the settings for this block.
        *
        * @property settings
        * @type Object
        */
        var settings = $.extend({
            block: this,
            calendar: null,
            currentlyShownEventDay: null,
            currentStartDate: null,
            initializing: true
        }, options);

        /**
        * All the functions for this block.
        *
        * @property functions
        * @type Object
        */
        var functions = {
            /**
            * @method initialize
            */
            initialize: () => {
                settings.calendar = new Calendar({
                    id: "#arr-calendar",
                    calendarSize: "small",
                    disableMonthYearPickers: true,
                    disableMonthArrowClick: true,
                    dateChanged: (currentDate) => functions.dateChanged(currentDate),
                    selectedDateClicked: (currentDate) => functions.dateChanged(currentDate),
                    monthChanged: (currentDate) => functions.update(currentDate)
                });

                settings.block.find(".fa-list").click(() => {
                    if (settings.currentlyShownEventDay) {
                        settings.currentlyShownEventDay.tooltip("destroy");
                    }
                    settings.block.find("#arr-calendar, .list").toggle();
                });

                settings.block.find(".fa-chevron-left").click(() => {
                    if (settings.currentlyShownEventDay) {
                        settings.currentlyShownEventDay.tooltip("destroy");
                    }
                    var previousMonth = new Date(settings.currentStartDate.getFullYear(), settings.currentStartDate.getMonth() - 1, 1);
                    settings.calendar.setDate(previousMonth);
                });
                settings.block.find(".fa-chevron-right").click(() => {
                    if (settings.currentlyShownEventDay) {
                        settings.currentlyShownEventDay.tooltip("destroy");
                    }
                    var nextMonth = new Date(settings.currentStartDate.getFullYear(), settings.currentStartDate.getMonth() + 1, 1);
                    settings.calendar.setDate(nextMonth);
                });
            },

            /**
             * Updates the current statistics by calling the Arr controller.
             * 
             * @method update
             * @param {boolean} initialize  Whether called on initialization or not.
             */
            update: (startDate) => {
                if (functions.formatDate(settings.currentStartDate) == functions.formatDate(startDate)) {
                    return;
                }

                settings.block.isLoading("show");
                settings.currentStartDate = startDate;
                var endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + 1);
                endDate.setDate(0);

                $.ajax({
                    url: "arr?start=" + functions.formatDate(startDate) + "&end=" + functions.formatDate(endDate),
                    dataType: "json",
                    success: (data) => {
                        settings.initializing = true;
                        settings.calendar.setEventsData(data);
                        functions.createList(data);
                    },
                    complete: () => {
                        settings.block.isLoading("hide");
                    }
                });
            },

            /**
             * Called by update to create a list of Sonarr and Radarr items.
             * 
             * @method createList
             * @param {object} data The data retrieved from the Arr controller.
             */
            createList: (data) => {
                let list = settings.block.find("ul");
                list.find("li:not(.clone)").remove();

                $.each(data, (_index, event) => {
                    let clone = list.find(".clone").clone();
                    let title = functions.getCompoundTitle([event]);
                    clone.find(".date span.hidden-sm").text(event.start.substring(0, 2));
                    clone.find(".date span:last-child").text(event.start.substring(2));
                    clone.find(".date i").addClass(event.type == "movie" ? "fa-clapperboard" : "fa-tv");
                    clone.find(".name").text(title).attr("title", title);
                    
                    clone.removeClass("clone hidden");
                    settings.block.find("ul").append(clone);
                });
            },

            /**
             * Called when the calendar changes date. Creates a new tooltip with Sonarr and Radarr items on that date.
             * 
             * @method dateChanged
             * @param Date currentDate  The currently selected date.
             * */
            dateChanged: (currentDate) => {
                if (!settings.calendar) {
                    return;
                }
                if (settings.initializing) {
                    settings.initializing = false;
                    return;
                }

                if (settings.currentlyShownEventDay) {
                    settings.currentlyShownEventDay.tooltip("destroy");
                }

                var currentDay = currentDate.getDate();
                var currentMonth = currentDate.getMonth() + 1 + "";
                var currentDate = currentDate.getFullYear() + "-" + zeropad(currentMonth, 2) + "-" + zeropad(currentDay, 2);
                var eventDay = functions.findDayByIndex(currentDay);
                var eventDayData = settings.calendar.eventsData.filter((date) => date.end == currentDate);

                if (settings.currentlyShownEventDay && (!eventDayData.length || settings.currentlyShownEventDay.data("day") == currentDay)) {
                    settings.currentlyShownEventDay.data("day", null);
                    return;
                }
                var title = functions.getCompoundTitle(eventDayData);
                eventDay.data("title", title);
                eventDay.data("day", currentDay);
                settings.currentlyShownEventDay = eventDay.tooltip({
                    html: true,
                    container: "body",
                    trigger: "manual",
                }).tooltip("toggle");
            },

            /**
             * Creates a title for a tooltip containing all the Radarr and Sonarr items.
             * 
             * @method getCompoundTitle
             * @param {object} eventDayData  Data containing the Radarr and Sonarr items for a date.
             * */
            getCompoundTitle: (eventDayData) => {
                var title = "";

                if (eventDayData.length == 1) {
                    title = functions.getTitle(eventDayData[0])
                }
                else if (eventDayData.length > 1) {
                    title = "<ul>";
                    $.each(eventDayData, (_index, eventData) => title += "<li>" + functions.getTitle(eventData) + "</li>");
                    title += "</ul>";
                }

                return title;
            },

            /**
             * Creates a title for a single Radarr or Sonarr item.
             * 
             * @method getTitle
             * @param {object} eventData The data for the Radarr or Sonarr item to get the title for.
             * */
            getTitle: (eventData) => {
                if (eventData.serie) {
                    return eventData.serie + " | S" + zeropad(eventData.seasonNumber, 2) + "E" + zeropad(eventData.episodeNumber, 2) + " - " + eventData.title;
                }
                return eventData.title;
            },

            /**
             * Given an index/day, retrieve the HTMLElement representing that day on the calendar.
             * 
             * @method findDayByIndex
             * @param {number} index    The current day index of the month.
             * */
            findDayByIndex: (index) => settings.block.find(".calendar__day-active").contents().filter(function () {
                return $(this).text().trim() == index;
            }).parent().find(".calendar__day-box"),

            /**
             * Given a date, format it as an ISO string.
             * 
             * @method formatDate
             * @param {Date} date   The date to format.
             * */
            formatDate: (date) => date ? date.toISOString().split("T")[0] : null
        };

        functions.initialize();

        return functions;
    };
})(jQuery);