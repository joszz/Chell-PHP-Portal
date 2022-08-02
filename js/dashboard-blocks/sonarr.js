"use strict";

/**
* The Opcache block on the dashboard.
*
* @class Opcache
* @module Dashboard
* @submodule DashboardBlocks
*/
(function ($) {
    $.fn.sonarr = function (options) {
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
                if (settings.block.length === 0) {
                    return;
                }

                settings.calendar = new Calendar({
                    id: "#sonarr-calendar",
                    calendarSize: "small",
                    dateChanged: (currentDate) => functions.dateChanged(currentDate),
                    selectedDateClicked: (currentDate) => functions.dateChanged(currentDate),
                    monthChanged: (currentDate) => functions.update(currentDate)
                });

                settings.block.find(".fa-list").click(() => {
                    settings.block.find("#sonarr-calendar, .list").toggle();
                });

                functions.update(new Date());
            },

            update: (startDate) => {
                if (functions.formatDate(settings.currentStartDate) == functions.formatDate(startDate)) {
                    return;
                }

                settings.currentStartDate = startDate;
                var endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + 1);
                endDate.setDate(0);

                $.ajax({
                    url: "sonarr?start=" + functions.formatDate(startDate) + "&end=" + functions.formatDate(endDate),
                    dataType: "json",
                    success: (data) => {
                        settings.initializing = true;
                        settings.calendar.setEventsData(data);
                        functions.createList(data);
                    },
                    complete: () => settings.block.isLoading("hide")
                });
            },

            createList: (data) => {
                let list = settings.block.find("ul");
                list.find("li:not(.clone)").remove();

                $.each(data, (_index, event) => {
                    let clone = list.find(".clone").clone();
                    let title = functions.getCompoundTitle([event]);
                    clone.find(".date").text(event.start);
                    clone.find(".name").text(title).attr("title", title);
                    clone.removeClass("clone hidden");
                    settings.block.find("ul").append(clone);
                });
            },

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

                if (!eventDayData.length || (settings.currentlyShownEventDay && settings.currentlyShownEventDay.data("day") == currentDay)) {
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

            getTitle: (eventData) => eventData.serie + " | S" + zeropad(eventData.seasonNumber, 2) + "E" + zeropad(eventData.episodeNumber, 2) + " - " + eventData.title,

            findDayByIndex: (index) => settings.block.find(".calendar__day-active").contents().filter(function () {
                return $(this).text().trim() == index;
            }).parent().find(".calendar__day-box"),

            formatDate: (date) => date ? date.toISOString().split("T")[0] : null
        };

        functions.initialize();

        return functions;
    };
})(jQuery);