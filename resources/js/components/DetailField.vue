<template>
  <div class="mt-4 mb-4 ml-4 mr-4" style="margin-top: 20px">
    <h4 class="font-normal text-80 mb-2">{{ this.field.name }}</h4>
    <h4 v-show="error" class="text-danger">
      {{ this.field.customErrorMessage }}
    </h4>
    <div
      v-show="!error"
      ref="map"
      class="rounded-sm"
      style="height: 600px; width: 100%"
    ></div>
    <div
      v-show="!error"
      ref="panel"
      class="mt-4"
      style="margin-left: -5%"
    ></div>
  </div>
</template>

<script>
import "here-js-api/scripts/mapsjs-core";
import "here-js-api/scripts/mapsjs-service";
import "here-js-api/scripts/mapsjs-ui";
import "here-js-api/scripts/mapsjs-mapevents";
import "here-js-api/scripts/mapsjs-clustering";

export default {
  props: ["resource", "resourceName", "resourceId", "field"],
  data() {
    return {
      error: false,
      map: null,
      routeInstructionsContainer: null,
      bubble: "",
      behavior: null,
      ui: null,
      gpspoints: [],
      gpsDriverPoints: [],
    };
  },
  methods: {
    calculateRouteFromAtoB(platform) {
      this.gpspoints = this.field.gpsPoints;
      //console.log(this.gpspoints);

      var router = platform.getRoutingService(),
        routeRequestParams = {
          mode: "fastest;car",
          representation: "display",
          routeattributes: "waypoints,summary,shape,legs",
          maneuverattributes: "direction,action",
        };




        var origin = this.gpspoints[0];
        var origin = JSON.parse(origin);
        if (origin.lat != 0 && origin.lon != 0) {
            routeRequestParams["origin"] =  origin.lat + "," + origin.lon;
        }

      this.gpspoints.forEach((element,index) => {
          console.log("Index");
          console.log(index);
        var jsonelement = JSON.parse(element);
        //console.log(jsonelement.lat + "," + jsonelement.lon);
        //  'via': new H.service.Url.MultiValueQueryParameter(['48.8664,2.3234', '48.8703,2.3499']),

        if (jsonelement.lat != 0 && jsonelement.lon != 0) {
            switch (index) {
                case this.gpspoints.length:
                    routeRequestParams["destination"] =
                    jsonelement.lat + "," + jsonelement.lon;
                break;
                default:
                    routeRequestParams["waypoint" + index] =
                    jsonelement.lat + "," + jsonelement.lon;
                break;
            }
        }

      });

      console.log("routeRequestParams");

      console.log(routeRequestParams);

      router.calculateRoute(routeRequestParams, this.onSuccess, this.onError);
    },
    onSuccess(result) {
      var route = result.response.route[0];
      /*
       * The styling of the route response on the map is entirely under the developer's control.
       * A representitive styling can be found the full JS + HTML code of this example
       * in the functions below:
       */
      this.addRouteShapeToMap(route);
      this.addManueversToMap(route);

       //this.addWaypointsToPanel(route.waypoint);
      if (this.field.showManuevers) {
        this.addManueversToPanel(route);
      }
      if (this.field.showSummary) {
        this.addSummaryToPanel(route.summary);
      }
    },
    onError(error) {
      console.log(error);
      alert("Can't reach the remote server");
    },
    openBubble(position, text) {
      if (!this.bubble) {
        this.bubble = new H.ui.InfoBubble(
          position,
          // The FO property holds the province name.
          { content: text }
        );
        this.ui.addBubble(this.bubble);
      } else {
        this.bubble.setPosition(position);
        this.bubble.setContent(text);
        this.bubble.open();
      }
    },
    addRouteShapeToMap(route) {
      var lineString = new H.geo.LineString(),
        routeShape = route.shape,
        polyline;

      routeShape.forEach(function (point) {
        var parts = point.split(",");
        lineString.pushLatLngAlt(parts[0], parts[1]);
      });

      // Create an outline for the route polyline:
      var routeOutline = new H.map.Polyline(lineString, {
        style: {
          lineWidth: 5,
          strokeColor: "rgba(0, 128, 255, 0.7)",
          lineTailCap: "arrow-tail",
          lineHeadCap: "arrow-head",
        },
      });
      // Create a patterned polyline:
      var routeArrows = new H.map.Polyline(lineString, {
        style: {
          // lineWidth: 10,
          // fillColor: 'white',
          // strokeColor: 'rgba(255, 255, 255, 1)',
          // lineDash: [0, 2],
          // lineTailCap: 'arrow-tail',
          // lineHeadCap: 'arrow-head'
        },
      });

      // Add the polyline to the map
      this.map.addObjects([routeOutline, routeArrows]);
      // And zoom to its bounding rectangle
      // map.getViewModel().setLookAtData({
      // bounds: polyline.getBoundingBox()
      // });
    },
    addManueversToMap(route) {
      var officeMarkup =
          '<svg xmlns="http://www.w3.org/2000/svg" style="fill:green;" viewBox="0 0 20 20"><path d="M11 7l1.44 2.16c.31.47 1.01.84 1.57.84H17V8h-3l-1.44-2.16a5.94 5.94 0 0 0-1.4-1.4l-1.32-.88a1.72 1.72 0 0 0-1.7-.04L4 6v5h2V7l2-1-3 14h2l2.35-7.65L11 14v6h2v-8l-2.7-2.7L11 7zm1-3a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>',
        officeIcon = new H.map.Icon(officeMarkup, { size: { w: 56, h: 56 } });

      var group = new H.map.Group(),
        i,
        j;
      console.log("gps driver points");
      console.log(this.gpspoints);
      var cargroup = new H.map.Group();
      var officedata = JSON.parse(this.gpspoints[0]);

      var marker = new H.map.Marker(
        {
          lat: officedata.lat,
          lng: officedata.lon,
        },
        {
          icon: officeIcon,
        }
      );
      marker.instruction = "Office";
      group.addObject(marker);

      // Add a marker for each maneuver

      // for (i = 1;  i < route.waypoint.length; i += 1) {
      //         var marker =  new H.map.Marker({
      //         lat: route.waypoint[i].originalPosition.latitude,
      //         lng: route.waypoint[i].originalPosition.longitude,
      //         }, {
      //         icon : dotIcon
      //         });
      //         marker.instruction = "Some details about the customer"+i;
      //         group.addObject(marker);

      // }
      var i = 1;
      if(this.field.routeFinishAtOrigin)
      {
          this.gpspoints.pop();
      }
      this.gpspoints.slice(1).forEach((element) => {
        var jsonelement = JSON.parse(element);
        if (jsonelement.lat != 0 && jsonelement.lon != 0) {
          var svgMarkup =
            '<svg  xmlns:dc="http://purl.org/dc/elements/1.1/"  xmlns:cc="http://creativecommons.org/ns#"    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"    xmlns:svg="http://www.w3.org/2000/svg"    xmlns="http://www.w3.org/2000/svg"    xmlns:sodipodi="http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd"    xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"    width="5.6444445mm"    height="9.847393mm"    viewBox="0 0 20 34.892337"    id="svg3455"    version="1.1"    inkscape:version="0.91 r13725"    sodipodi:docname="Map Pin.svg">   <defs      id="defs3457" />   <sodipodi:namedview      id="base"      pagecolor="#ffffff"      bordercolor="#666666"      borderopacity="1.0"      inkscape:pageopacity="0.0"      inkscape:pageshadow="2"      inkscape:zoom="12.181359"      inkscape:cx="8.4346812"      inkscape:cy="14.715224"      inkscape:document-units="px"      inkscape:current-layer="layer1"      showgrid="false"      inkscape:window-width="1024"      inkscape:window-height="705"      inkscape:window-x="-4"      inkscape:window-y="-4"      inkscape:window-maximized="1"      fit-margin-top="0"      fit-margin-left="0"      fit-margin-right="0"      fit-margin-bottom="0" />   <metadata      id="metadata3460">     <rdf:RDF>       <cc:Work          rdf:about="">         <dc:format>image/svg+xml</dc:format>         <dc:type            rdf:resource="http://purl.org/dc/dcmitype/StillImage" />         <dc:title></dc:title>       </cc:Work>     </rdf:RDF>   </metadata>   <g      inkscape:label="Layer 1"      inkscape:groupmode="layer"      id="layer1"      transform="translate(-814.59595,-274.38623)">     <g        id="g3477"        transform="matrix(1.1855854,0,0,1.1855854,-151.17715,-57.3976)">       <path          sodipodi:nodetypes="sscccccsscs"          inkscape:connector-curvature="0"          id="path4337-3"          d="m 817.11249,282.97118 c -1.25816,1.34277 -2.04623,3.29881 -2.01563,5.13867 0.0639,3.84476 1.79693,5.3002 4.56836,10.59179 0.99832,2.32851 2.04027,4.79237 3.03125,8.87305 0.13772,0.60193 0.27203,1.16104 0.33416,1.20948 0.0621,0.0485 0.19644,-0.51262 0.33416,-1.11455 0.99098,-4.08068 2.03293,-6.54258 3.03125,-8.87109 2.77143,-5.29159 4.50444,-6.74704 4.56836,-10.5918 0.0306,-1.83986 -0.75942,-3.79785 -2.01758,-5.14062 -1.43724,-1.53389 -3.60504,-2.66908 -5.91619,-2.71655 -2.31115,-0.0475 -4.4809,1.08773 -5.91814,2.62162 z"          style="display:inline;opacity:1;fill:#ff4646;fill-opacity:1;stroke:#d73534;stroke-width:1;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1" />       <circle          r="6.0355"          cy="288.25278"          cx="823.03064"          id="path3049"          style="display:inline;opacity:1;fill:#590000;fill-opacity:1;stroke-width:0" />     </g>   </g>   		 <text x="49%" y="37%"           text-anchor="middle" 		  font-size = "9" 		  fill = "white"           >' +
            i +
            "</text> </svg> ";
          var dotIcon = new H.map.Icon(svgMarkup, { size: { w: 56, h: 56 } });
          var marker = new H.map.Marker(
            {
              lat: jsonelement.lat,
              lng: jsonelement.lon,
            },
            {
              icon: dotIcon,
            }
          );
          marker.instruction = jsonelement.description;
          group.addObject(marker);
        }
        i = i + 1;
      });
      console.log("gps driver points");
      console.log(this.field.gpsDriverPoints);
      var i = 1;
      this.gpsDriverPoints.forEach((element) => {
        var jsonelement = JSON.parse(element);
        var x ='';
            if(i<10){
                x = '280';

            }
            if(i>9)
            {
                x = '260';
            }
        if (jsonelement.lat != 0 && jsonelement.lon != 0) {
          if (this.field.svgCar) {
            var svgCar = this.field.svgCar +'<text x="'+x+'" y="150" font-family="Verdana" font-size="100" font-weight="bold" fill="black">'+ i + "</text></svg>";
          } else {
            var svgCar =
              '<svg width="496.246" height="496.246" viewBox="-36 1 511 511.99999" xmlns="http://www.w3.org/2000/svg"> <path d="m257.128906 452.128906c0-33.066406-26.847656-59.871094-59.96875-59.871094s-59.96875 26.804688-59.96875 59.871094 26.847656 59.871094 59.96875 59.871094 59.96875-26.804688 59.96875-59.871094zm0 0" fill="#f7c92b"/><path d="m197.160156 392.257812c-6.0625 0-11.914062.90625-17.429687 2.578126 24.617187 7.453124 42.539062 30.277343 42.539062 57.292968 0 27.011719-17.921875 49.839844-42.539062 57.292969 5.515625 1.671875 11.367187 2.578125 17.429687 2.578125 33.121094 0 59.96875-26.804688 59.96875-59.871094s-26.847656-59.871094-59.96875-59.871094zm0 0" fill="#f2b51d"/><path d="m204.167969 102.699219c-19.261719 3.863281-38.007813-8.589844-41.878907-27.820313-3.871093-19.226562 8.605469-37.945312 27.863282-41.808594 19.257812-3.863281 38.007812 8.589844 41.878906 27.820313 3.867188 19.226563-8.605469 37.945313-27.863281 41.808594zm0 0" fill="#f7c92b"/><path d="m232.03125 60.890625c-3.871094-19.230469-22.621094-31.683594-41.878906-27.820313-1.792969.359376-3.523438.851563-5.191406 1.453126 11.242187 4.109374 20.144531 13.789062 22.675781 26.367187 3.511719 17.4375-6.425781 34.457031-22.671875 40.355469 5.945312 2.171875 12.542968 2.789062 19.203125 1.453125 19.257812-3.863281 31.730469-22.582031 27.863281-41.808594zm0 0" fill="#f2b51d"/><path d="m345.691406 442.109375h-297.0625c-26.582031 0-48.128906-21.511719-48.128906-48.046875 0-14.691406 6.660156-28.59375 18.113281-37.816406l9.542969-7.6875c22.074219-17.773438 34.910156-44.566406 34.910156-72.878906v-50.496094c0-74.503906 60.492188-134.898438 135.117188-134.898438 74.621094 0 135.117187 60.394532 135.117187 134.898438v51.429687c0 27.777344 12.355469 54.125 33.726563 71.914063l9.292968 7.734375c11.089844 9.230469 17.5 22.902343 17.5 37.316406v.484375c0 26.535156-21.546874 48.046875-48.128906 48.046875zm0 0" fill="#f7e249"/><path d="m376.320312 356.261719-9.292968-7.734375c-21.371094-17.792969-33.726563-44.136719-33.726563-71.917969v-51.429687c0-74.5-60.496093-134.898438-135.117187-134.898438-13.222656 0-25.996094 1.90625-38.070313 5.441406 56.085938 16.410156 97.046875 68.15625 97.046875 129.457032v51.429687c0 27.78125 12.355469 54.125 33.726563 71.917969l9.296875 7.734375c11.085937 9.230469 17.5 22.902343 17.5 37.316406v.484375c0 26.535156-21.546875 48.046875-48.128906 48.046875h76.140624c26.578126 0 48.125-21.511719 48.125-48.046875v-.484375c0-14.414063-6.410156-28.085937-17.5-37.316406zm0 0" fill="#f7c92b"/><path d="m440.078125 115.777344c0-63.941406-51.917969-115.777344-115.964844-115.777344s-115.96875 51.835938-115.96875 115.777344 51.921875 115.777344 115.96875 115.777344 115.964844-51.835938 115.964844-115.777344zm0 0" fill="#fc476e"/> <path d="m355.230469 4.222656c29.574219 20.980469 48.867187 55.460938 48.867187 94.441406 0 63.941407-51.917968 115.777344-115.964844 115.777344-10.78125 0-21.214843-1.472656-31.117187-4.222656 18.9375 13.4375 42.09375 21.335938 67.097656 21.335938 64.042969 0 115.964844-51.835938 115.964844-115.777344 0-53.179688-35.914063-97.976563-84.847656-111.554688zm0 0" fill="#cc2b5a"/> <text x="'+x+'" y="150" font-family="Verdana" font-weight="bold" font-size="100" fill="black">' +
              i +
              "</text></svg>";
          }
          var dotIcon = new H.map.Icon(svgCar, { size: { w: 56, h: 56 } });
          var marker = new H.map.Marker(
            {
              lat: jsonelement.lat,
              lng: jsonelement.lon,
            },
            {
              icon: dotIcon,
            }
          );
          marker.instruction = jsonelement.description;
          cargroup.addObject(marker);
        }
        i = i + 1;
      });

      group.addEventListener(
        "tap",
        function (evt) {
          this.map.setCenter(evt.target.getGeometry());
          this.openBubble(
            evt.target.getGeometry(),
            evt.target.instruction,
            this.ui
          );
        }.bind(this),
        false
      );

      cargroup.addEventListener(
        "tap",
        function (evt) {
          this.map.setCenter(evt.target.getGeometry());
          this.openBubble(
            evt.target.getGeometry(),
            evt.target.instruction,
            this.ui
          );
        }.bind(this),
        false
      );

    if (this.gpspoints.length < 2) {
            this.map.setCenter({
        lat: JSON.parse(this.gpspoints[0]).lat,
        lng: JSON.parse(this.gpspoints[0]).lon,
      });
    }
    else{
      this.map.setCenter({
        lat: route.waypoint[0].originalPosition.latitude,
        lng: route.waypoint[0].originalPosition.longitude,
      });
    }
      this.map.setZoom(9);
      // Add the maneuvers group to the map
      this.map.addObject(group);
      this.map.addObject(cargroup);
      if (this.field.circle_lat && this.field.circle_lng && this.field.circle_radius) {
            this.map.addObject(new H.map.Circle(
                // The central point of the circle
                {lat:this.field.circle_lat, lng:this.field.circle_lng},
                // The radius of the circle in meters
                this.field.circle_radius,
                {
                style: {
                    strokeColor: this.field.circle_stroke_color,// Color of the perimeter
                    lineWidth: this.field.line_width,
                    fillColor: this.field.circle_color // Color of the circle
                }
                }
            ));
      }
    },
    addWaypointsToPanel(waypoints) {
      var nodeH3 = document.createElement("h3"),
        waypointLabels = [],
        i;

      for (i = 0; i < waypoints.length; i += 1) {
        waypointLabels.push(waypoints[i].label);
      }

      nodeH3.textContent = waypointLabels.join(" - ");

      this.routeInstructionsContainer.innerHTML = "";
      this.routeInstructionsContainer.appendChild(nodeH3);
    },
    addSummaryToPanel(summary) {
      var summaryDiv = document.createElement("div"),
        content = "";
      var total = summary.distance * 0.001;
      if (this.field.showdistance) {
        content += "<b>Total distance</b>: " + total.toFixed(2) + "km. <br/>";
      }
      if (this.field.time) {
        content +=
          "<b>Travel Time</b>: " +
          this.toMMSS(summary.travelTime) +
          " (in current traffic)";
      }

      summaryDiv.style.fontSize = "20px";
      summaryDiv.style.marginLeft = "5%";
      summaryDiv.style.marginRight = "5%";
      summaryDiv.innerHTML = content;
      this.routeInstructionsContainer.appendChild(summaryDiv);
    },
    addManueversToPanel(route) {
      var nodeOL = document.createElement("ol"),
        i,
        j;

      nodeOL.style.fontSize = "small";
      nodeOL.style.marginLeft = "3%";
      nodeOL.style.marginRight = "5%";
      nodeOL.style.marginBottom = "5%";
      nodeOL.className = "directions";

      // Add a marker for each maneuver
      for (i = 0; i < route.leg.length; i += 1) {
        for (j = 0; j < route.leg[i].maneuver.length; j += 1) {
          // Get the next maneuver.
          var maneuver = route.leg[i].maneuver[j];

          var li = document.createElement("li"),
            spanArrow = document.createElement("span"),
            spanInstruction = document.createElement("span");

          spanArrow.className = "arrow " + maneuver.action;
          spanInstruction.innerHTML = maneuver.instruction;
          li.appendChild(spanArrow);
          li.appendChild(spanInstruction);

          nodeOL.appendChild(li);
        }
      }

      this.routeInstructionsContainer.appendChild(nodeOL);
    },
    toMMSS(time) {
      return Math.floor(time / 60) + " minutes " + (time % 60) + " seconds.";
    },
  },
  mounted() {
    if (this.field.gpsPoints.length < 1) {
      alert(this.field.customErrorMessage);
      this.error = true;
      return false;
    }

    this.gpsDriverPoints = this.field.gpsDriverPoints;
    this.gpspoints = this.field.gpsPoints;

    this.routeInstructionsContainer = this.$refs.panel;

    var platform = new H.service.Platform({
      apikey: this.field.apiKey,
    });

    var defaultLayers = platform.createDefaultLayers();
    var map = new H.Map(this.$refs.map, defaultLayers.vector.normal.map);
    // add a resize listener to make sure that the map occupies the whole container
    window.addEventListener("resize", () => map.getViewPort().resize());
    this.map = map;
    this.behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));

    // Create the default UI components
    this.ui = H.ui.UI.createDefault(this.map, defaultLayers);
    //this.calculateRouteFromAtoB(platform);
    if (this.field.gpsPoints.length == 1) {
    this.addManueversToMap();
    }else{
        this.calculateRouteFromAtoB(platform);
    }
  },
};
</script>
<style>
/*
 * Explanation why the layout looks so complicated:
 * The UI container needs a position (absolute or relative) to prevent z-index issues (DomMarker on top of UI)
 * Therefore it has these additional styles:
 *    position: absolute;
 *    width: 100%;
 *    height: 100%;
 * To prevent that the UI container captures all events the container is displaced by
 *   left: 100%;
 * To neutralize the displacement for the UI elements within the UI container the following adjustments are needed:
 *  - InfoBubble (.H_ib):            left: -100%;
 *  - left anchor (.H_l_left):       margin-left: -100%;
 *  - center anchor (.H_l_center):   left: -50%;            (was left: 50%)
 *  - right anchor (.H_l_right):     right: 100%;           (was right: 0)
 *                                        margin-left: -100%;
 */

.H_ui {
  font-size: 10px;
  font-family: "Lucida Grande", Arial, Helvetica, sans-serif;
  -moz-user-select: none;
  -khtml-user-select: none;
  -webkit-user-select: none;
  -o-user-select: none;
  -ms-user-select: none;
  /* position ui on top of imprint to make both clickable */
  z-index: 2;
  position: absolute;
  width: 100%;
  height: 100%;
  left: 100%;
}
.H_ui * {
  /* normalize in case some other normalization CSS likes things differently */
  box-sizing: content-box;
  -moz-box-sizing: content-box;
}
.H_noevs {
  pointer-events: none;
}

/*
 * Layout
 */
.H_l_left {
  position: absolute;
  left: 16px;
  margin-left: -100%;
}
.H_l_center {
  position: absolute;
  left: -50%;
}
.H_l_right {
  position: absolute;
  right: calc(100% + 16px);
  margin-left: -100%;
}
.H_l_top {
  top: 16px;
}
.H_l_middle {
  top: 50%;
}
.H_l_bottom {
  bottom: 16px;
}

/* Fix MAPSJS-579 for modern browsers */
[class^="H_l_"] {
  pointer-events: none;
}
.H_ctl {
  /* hack for IE9-10, auto doesn't work for them */
  pointer-events: visiblePainted;
  pointer-events: auto;
}

.H_l_horizontal .H_ctl {
  float: left;
}

.H_l_anchor {
  clear: both;
  float: right;
}

.H_l_vertical .H_ctl {
  clear: both;
}

.H_l_right .H_l_vertical .H_ctl {
  float: right;
}

.H_l_right.H_l_middle.H_l_vertical .H_ctl {
  float: right;
}

/**
 *  Element styles
 */

.H_ctl {
  margin: 0.8em;
  position: relative;
  -ms-touch-action: none;
}

.H_btn {
  cursor: pointer;
}

.H_grp .H_btn,
.H_rdo_buttons .H_btn {
  box-shadow: none;
}
.H_grp .H_btn.H_active,
.H_rdo_buttons .H_btn.H_active {
  background: none;
}

.H_btn {
  box-shadow: 0em 0 0.4em 0 rgba(15, 22, 33, 0.6);
  border-radius: 0.5em;
  width: 4em;
  height: 4em;
  background: #fff;
}

.H_disabled,
.H_disabled:hover {
  cursor: default;
}

.H_rdo_title {
  font-size: 14px;
  height: 40px;
  line-height: 40px;
  background-color: #575b63;
  color: #fff;
  padding-left: 16px;
  padding-right: 16px;
  border-radius: 5px 5px 0 0;
  margin-bottom: 8px;
  cursor: default;
}
.H_rdo ul {
  list-style: none;
  margin: 0 auto;
  padding: 0;
}

/**
 *   Base Elements
 */
.H_ctl.H_grp {
  background: #fff;
  box-shadow: 0em 0 0.4em 0 rgba(15, 22, 33, 0.6);
  border-radius: 0.5em;
}
/* Button divider */
.H_zoom .H_el {
  position: relative;
}
.H_l_vertical .H_zoom .H_el:last-child::after,
.H_l_horizontal .H_zoom .H_el:last-child::after {
  content: none;
}

.H_l_vertical .H_zoom .H_el {
  margin-bottom: 0.1em;
}
.H_l_vertical .H_zoom .H_el:last-child {
  margin-bottom: 0;
}
.H_l_vertical .H_zoom .H_el::after {
  content: "";
  position: absolute;
  width: 2.6em;
  height: 0.1em;
  bottom: -0.1em;
  left: 0.7em;
  background: rgba(15, 22, 33, 0.1);
}

.H_l_horizontal .H_zoom .H_el {
  margin-right: 0.1em;
}
.H_l_horizontal .H_zoom .H_el:last-child {
  margin-right: 0;
}
.H_l_horizontal .H_zoom .H_el::after {
  content: "";
  position: absolute;
  width: 0.1em;
  height: 2.6em;
  top: 0.7em;
  right: -0.1em;
  background: rgba(15, 22, 33, 0.1);
}
/* End: Button divider */
.H_l_horizontal .H_grp .H_btn,
.H_l_vertical .H_ctl {
  float: left;
}

/** Menu panel */
.H_overlay {
  font-size: 14px;
  color: rgba(15, 22, 33, 0.6);
  box-shadow: 0px 0 4px 0 rgba(15, 22, 33, 0.6);
  border-radius: 5px;
  position: absolute;
  min-width: 200px;
  background: #fff;
  display: none;
  z-index: 100;
  padding-bottom: 4px;
}

.H_overlay .H_separator {
  content: "";
  position: relative;
  display: block;
  margin: 8px 16px 8px 16px;
  height: 1px;
  background: rgba(15, 22, 33, 0.1);
}

.H_overlay .H_btn,
.H_overlay .H_rdo li {
  width: 184px;
  height: 24px;
  line-height: 24px;
  padding: 8px 16px;
}
.H_overlay .H_btn {
  border-radius: 0px;
}

.H_overlay .H_btn:hover,
.H_overlay .H_rdo li:hover {
  color: rgba(15, 22, 33, 0.8);
}

.H_overlay .H_btn.H_disabled,
.H_overlay .H_rdo.H_disabled li,
.H_overlay .H_btn.H_disabled:hover,
.H_overlay .H_rdo.H_disabled li:hover {
  color: rgba(15, 22, 33, 0.2);
}

.H_overlay .H_btn.H_active,
.H_overlay .H_rdo.H_active li,
.H_overlay .H_btn.H_active:hover,
.H_overlay .H_rdo.H_active li:hover {
  color: #0f1621;
}

.H_overlay > *:last-child {
  clear: both;
}
.H_overlay > .H_btn {
  white-space: nowrap;
}

.H_overlay.H_open {
  display: block;
}

.H_overlay::before,
.H_overlay::after {
  content: " ";
  width: 0;
  height: 0;
  border-style: solid;
  position: absolute;
}
.H_overlay.H_left::before {
  border-width: 10px 10px 10px 0;
  border-color: transparent rgba(15, 22, 33, 0.2) transparent transparent;
  left: -12px;
}
.H_overlay.H_left::after {
  border-width: 10px 10px 10px 0;
  border-color: transparent #fff transparent transparent;
  left: -10px;
}
.H_overlay.H_top.H_left::after {
  border-color: transparent #575b63 transparent transparent;
}

.H_overlay.H_right::before {
  border-width: 10px 0 10px 10px;
  border-color: transparent transparent transparent rgba(15, 22, 33, 0.2);
  left: calc(100% + 1px);
}
.H_overlay.H_right::after {
  border-width: 10px 0 10px 10px;
  border-color: transparent transparent transparent #fff;
  left: 100%;
}
.H_overlay.H_top.H_right::after {
  border-color: transparent transparent transparent #575b63;
}

.H_overlay.H_top::before,
.H_overlay.H_top::after {
  top: 10px;
}
.H_overlay.H_bottom::before,
.H_overlay.H_bottom::after {
  bottom: 10px;
}
.H_overlay.H_middle::before,
.H_overlay.H_middle::after {
  top: 50%;
  margin-top: -10px;
}

.H_overlay.H_top.H_center::before {
  border-width: 0 10px 10px 10px;
  border-color: transparent transparent rgba(15, 22, 33, 0.2) transparent;
  top: -11px;
  left: 50%;
  margin-left: -10px;
}
.H_overlay.H_top.H_center::after {
  border-width: 0 10px 10px 10px;
  border-color: transparent transparent #575b63 transparent;
  top: -10px;
  left: 50%;
  margin-left: -10px;
}

.H_overlay.H_bottom.H_center::before {
  border-width: 10px 10px 0 10px;
  border-color: rgba(15, 22, 33, 0.2) transparent transparent transparent;
  bottom: -11px;
  left: 50%;
  margin-left: -10px;
}
.H_overlay.H_bottom.H_center::after {
  border-width: 10px 10px 0 10px;
  border-color: #fff transparent transparent transparent;
  bottom: -10px;
  left: 50%;
  margin-left: -10px;
}

/** InfoBubble */
.H_ib {
  position: absolute;
  left: 0.91em;
  left: -100%;
}
.H_ib_tail {
  position: absolute;
  width: 20px;
  height: 10px;
  margin: -10px -10px;
}

.H_ib_tail::before {
  bottom: -1px;
  border-width: 10px;
  position: absolute;
  display: block;
  content: "";
  border-color: transparent;
  border-style: solid;
  right: 0px;
}

.H_ib_tail::after {
  bottom: 0;
  position: absolute;
  display: block;
  content: "";
  border-color: white;
  border-style: solid;
  border-width: 10px;
}

.H_ib.H_ib_top .H_ib_tail::after {
  border-width: 10px 10px 0px 10px;
  border-color: white transparent;
}

.H_ib.H_ib_top .H_ib_tail::before {
  border-top-color: rgba(15, 22, 33, 0.2);
  border-bottom-width: 0px;
}

.H_ib_notail .H_ib_tail {
  display: none;
}
.H_ib_body {
  background: white;
  position: absolute;
  bottom: 0.5em;
  padding: 0;
  right: 0px;
  border-radius: 5px;
  margin-right: -3em;
  box-shadow: 0px 0 4px 0 rgba(15, 22, 33, 0.6);
  margin-bottom: 0.5em;
}

.H_ib_close {
  font-size: 0.6em;
  position: absolute;
  right: 12px;
  width: 12px;
  height: 12px;
  cursor: pointer;
  top: 12px;
  z-index: 1;
  background: none;
  box-shadow: none;
}

.H_ib_close svg.H_icon {
  top: 0;
  transform: none;
  width: auto;
  height: auto;
}

.H_ib_noclose .H_ib_close {
  display: none;
}
.H_ib_noclose .H_ib_body {
  padding: 0 0 0 0;
}

.H_ib_content {
  min-width: 6em;
  font: 14px "Lucida Grande", Arial, Helvetica, sans-serif;
  line-height: 24px;
  margin: 16px 28px 20px 16px;
  color: rgba(15, 22, 33, 0.8);
  user-select: text;
  -moz-user-select: text;
  -khtml-user-select: text;
  -webkit-user-select: text;
  -o-user-select: text;
  -ms-user-select: text;
}

/*##################################################  SLIDER  ########################################################*/

.H_l_horizontal .H_zoom_slider {
  min-width: 262px;
}
.H_slider {
  cursor: pointer;
}
.H_l_horizontal.H_slider {
  float: left;
  height: 4em;
  width: auto;
  padding: 0 1em;
}

.H_slider .H_slider_track {
  width: 0.4em;
  height: 100%;
}

.H_l_horizontal.H_slider .H_slider_track {
  height: 0.4em;
  width: 100%;
}

.H_l_horizontal.H_slider .H_slider_cont {
  height: 100%;
}

.H_l_horizontal.H_slider .H_slider_knob_cont {
  margin-top: -0.4em;
}

.H_slider {
  background-color: #fff;
  padding: 1em 0em;
  width: 4em;
}

.H_slider .H_slider_cont {
  position: relative;
}

.H_slider .H_slider_knob_cont,
.H_slider .H_slider_knob_halo {
  width: 1.8em;
  height: 1.8em;
  margin-left: 0em;
  border-radius: 9em;
}

.H_slider .H_slider_knob {
  width: 1.2em;
  height: 1.2em;
  background-color: white;
  border-radius: 9em;
  -webkit-transform: translate(-50%, -50%);
  -ms-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
  top: 50%;
  left: 50%;
  box-shadow: 0em 0 0.5em 0 rgba(15, 22, 33, 0.6);
  position: absolute;
}

.H_slider:hover .H_slider_knob {
  box-shadow: 0em 0 0.5em 0 rgba(15, 22, 33, 0.8);
}
.H_slider.H_disabled .H_slider_knob {
  box-shadow: 0em 0 0.5em 0 rgba(15, 22, 33, 0.2);
}
.H_slider.H_slider_active .H_slider_knob {
  box-shadow: 0em 0 0.5em 0 rgba(15, 22, 33, 1);
}

.H_slider:hover .H_slider_track {
  background-color: rgba(15, 22, 33, 0.8);
}

.H_disabled .H_slider_track {
  background-color: rgba(15, 22, 33, 0.2);
}
.H_slider:hover .H_slider_track_active {
  background-color: rgba(0, 182, 178, 0.8);
}
.H_disabled .H_slider_track_active {
  background-color: rgba(0, 182, 178, 0.2);
}
.H_slider.H_slider_active .H_slider_track {
  background-color: rgba(15, 22, 33, 1);
}
.H_slider.H_slider_active .H_slider_track_active {
  background-color: rgba(0, 182, 178, 1);
}

.H_slider .H_slider_track,
.H_slider .H_slider_knob_cont {
  position: relative;
  top: 50%;
  left: 50%;
  -webkit-transform: translate(-50%, -50%);
  -ms-transform: translate(-50%, -50%);
  transform: translate(-50%, -50%);
}

.H_slider .H_slider_track {
  background-color: rgba(15, 22, 33, 0.6);
  overflow: hidden;
}
.H_slider .H_slider_track_active {
  background-color: #00b6b2;
  position: absolute;
  transform: translate(-50%, 0%);
}

.H_slider.H_disabled .H_slider_track {
  background-color: rgba(15, 22, 33, 0.2);
}
.H_slider.H_disabled .H_slider_track_active {
  background-color: #bae2e3;
}

.H_slider.H_l_horizontal .H_slider_track_active {
  transform: translate(-200%, -50%);
}

.H_slider.H_disabled {
  cursor: default;
}

/*###############################################  CONTEXT MENU  #####################################################*/
.H_context_menu {
  font-size: 14px;
  min-width: 158px;
  max-width: 40%;
  box-shadow: 0em 0 0.4em 0 rgba(15, 22, 33, 0.6);
  position: absolute;
  left: -100%;
  top: 0;
  color: rgba(15, 22, 33, 0.6);
  background-color: #fff;
  -moz-border-radius: 5px;
  -webkit-border-radius: 5px;
  -o-border-radius: 5px;
  border-radius: 5px;
  padding: 16px 16px 4px 16px;
  -moz-user-select: initial;
  -khtml-user-select: initial;
  -webkit-user-select: initial;
  -o-user-select: initial;
  -ms-user-select: initial;
  z-index: 200;
}

.H_context_menu_closed {
  display: none;
}

.H_context_menu_item {
  text-overflow: ellipsis;
  overflow: hidden;
  line-height: 24px;
  margin-bottom: 16px;
  outline: none;
}

.H_context_menu_item.clickable:hover {
  color: rgba(15, 22, 33, 0.8);
  cursor: pointer;
}
.H_context_menu_item.disabled:hover,
.H_context_menu_item.disabled {
  background: transparent !important;
  color: rgba(15, 22, 33, 0.2);
  cursor: default !important;

  -moz-user-select: none;
  -khtml-user-select: none;
  -webkit-user-select: none;
  -o-user-select: none;
  -ms-user-select: none;
}
.H_context_menu_item_separator {
  height: 0;
  border-top: 1px solid rgba(15, 22, 33, 0.1);
  padding-bottom: 16px;
  line-height: 0;
  font-size: 0;
}

/*#################################################  SCALE BAR  ######################################################*/
.H_scalebar {
  margin-top: 36px;
  box-shadow: none;
  display: flex;
  align-items: center;
  text-shadow: -1px -1px 0 rgba(255, 255, 255, 0.7),
    1px -1px 0 rgba(255, 255, 255, 0.7), -1px 1px 0 rgba(255, 255, 255, 0.7),
    1px 1px 0 rgba(255, 255, 255, 0.7);
}

/*###################################  DISTANCE MEASUREMENT AND TRAFFIC INCIDENTS ####################################*/

.H_tib_content {
  width: 25em;
  position: relative;
  margin: -16px -28px -20px -16px;
}
.H_tib .H_tib_desc {
  padding: 0px 16px 20px 16px;
}
.H_tib .H_tib_time {
  color: rgba(15, 22, 33, 0.4);
  margin-top: 0.8em;
}
.H_tib_right {
  float: right;
}

.H_tib .H_btn > svg.H_icon {
  fill: rgba(255, 255, 255, 0.6);
}

.H_tib .H_btn:hover > svg.H_icon {
  fill: white;
}

.H_dm_label {
  font: 12px "Lucida Grande", Arial, Helvetica, sans-serif;
  color: black;
  text-shadow: 1px 1px 0.5px #fff, 1px -1px 0.5px #fff, -1px 1px 0.5px #fff,
    -1px -1px 0.5px #fff;
  white-space: nowrap;
  margin-left: 12px;
  margin-top: -7px;
  /* This will not work on IE9, but it is accepted! */
  pointer-events: none;
}

/*###################################################  ICON  #########################################################*/
svg.H_icon {
  display: block;
  position: relative;
  top: 50%;
  transform: translateY(-50%);
  margin: auto;
  width: 24px;
  height: 24px;
  fill: rgba(15, 22, 33, 0.6);
}
svg.H_icon .H_icon_stroke {
  stroke: rgba(15, 22, 33, 0.6);
  fill: none;
}
.H_btn:hover > svg.H_icon {
  fill: rgba(15, 22, 33, 0.8);
}
.H_btn:hover > svg.H_icon .H_icon_stroke {
  stroke: rgba(15, 22, 33, 0.8);
}
.H_btn.H_active {
  background-color: #cfd0d3;
}
.H_rdo .H_btn.H_active {
  background: none;
}

.H_active > svg.H_icon,
.H_active:hover > svg.H_icon {
  fill: #0f1621 !important;
}
.H_active > svg.H_icon .H_icon_stroke,
.H_active:hover > svg.H_icon .H_icon_stroke {
  stroke: #0f1621;
}

.H_disabled svg.H_icon,
.H_disabled:hover svg.H_icon {
  fill: rgba(15, 22, 33, 0.2);
  cursor: default;
}
.H_disabled svg.H_icon .H_icon_stroke,
.H_disabled:hover svg.H_icon .H_icon_stroke {
  stroke: rgba(15, 22, 33, 0.2);
}

/*###############################################  OVERVIEW MAP  #####################################################*/
.H_overview {
  transition: width 0.2s, height 0.2s, margin-top 0.2s, padding 0.2s;
  width: 0em;
  height: 0em;
  overflow: hidden;
  cursor: default;
  position: absolute;
  margin: auto;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  box-shadow: 0em 0 0.4em 0 rgba(15, 22, 33, 0.6);
}

.H_l_vertical .H_overview_active {
  margin: auto 5px;
}

.H_l_horizontal .H_overview_active {
  margin: 5px auto;
}

.H_l_center .H_overview {
  left: -9999px;
  right: -9999px;
}

.H_l_middle .H_overview {
  top: -9999px;
  bottom: -9999px;
}

.H_l_right .H_overview {
  right: 100%;
}

.H_l_left .H_overview {
  left: 100%;
}

.H_l_bottom .H_overview {
  bottom: 0;
}
.H_l_center.H_l_bottom .H_overview {
  bottom: 100%;
}

.H_l_top .H_overview {
  top: 0;
}
.H_l_center.H_l_top .H_overview {
  top: 100%;
}

.H_overview .H_overview_map {
  background-color: rgba(256, 256, 256, 0.6);
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
  box-shadow: 0em 0 0.4em 0 rgba(15, 22, 33, 0.6);
}

.H_overview_map .H_ui {
  display: none;
}

.H_zoom_lasso {
  position: absolute;
  display: none;
  box-shadow: 0em 0 0.4em 0 rgba(15, 22, 33, 0.6);
  z-index: 100000;
  background-color: rgba(15, 22, 33, 0.2);
}
</style>
