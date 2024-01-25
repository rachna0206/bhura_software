<?php
include 'header.php';
?>
<style>
    #mapCanvas{
    width: 100%;
    height: 400px;
}</style>
<div id="mapCanvas"></div>
<script src="https://maps.googleapis.com/maps/api/js?callback=initMap&key=AIzaSyBGpGP5g1Vpu00Ipsp0HTqQHyGtVWVgSxc" defer></script>
<script>
// Initialize and add the map
function initMap() {
    var map;
   
    var bounds = new google.maps.LatLngBounds();
    var mapOptions = {
        mapTypeId: 'roadmap'
    };
                    
    // Display a map on the web page
    map = new google.maps.Map(document.getElementById("mapCanvas"), mapOptions);
    map.setTilt(50);    

    var est;
        $.ajax({
            async: true,
            type: "POST",
            dataType: "json",
            url: "ajaxdata.php?action=estate_locations",
            data: "",
            cache: false,
            success: function(result){
            // console.log(result);
             markers=result;
            

             // Info window content
    /*var infoWindowContent = [
        ['<div class="info_content">' +
        '<h2>Brooklyn Museum</h2>' +
        '<h3>200 Eastern Pkwy, Brooklyn, NY 11238</h3>' +
        '<p>The Brooklyn Museum is an art museum located in the New York City borough of Brooklyn.</p>' + 
        '</div>'],
        ['<div class="info_content">' +
        '<h2>Central Library</h2>' +
        '<h3>10 Grand Army Plaza, Brooklyn, NY 11238</h3>' +
        '<p>The Central Library is the main branch of the Brooklyn Public Library, located at Flatbush Avenue.</p>' +
        '</div>'],
        ['<div class="info_content">' +
        '<h2>Prospect Park Zoo</h2>' +
        '<h3>450 Flatbush Ave, Brooklyn, NY 11225</h3>' +
        '<p>The Prospect Park Zoo is a 12-acre zoo located off Flatbush Avenue on the eastern side of Prospect Park, Brooklyn, New York City.</p>' +
        '</div>'],
        ['<div class="info_content">' +
        '<h2>Barclays Center</h2>' +
        '<h3>620 Atlantic Ave, Brooklyn, NY 11217</h3>' +
        '<p>Barclays Center is a multi-purpose indoor arena in the New York City borough of Brooklyn.</p>' +
        '</div>']
    ];*/
        
    // Add multiple markers to map
    var infoWindow = new google.maps.InfoWindow(), marker, i;
    
    // Place each marker on the map  
    for( i = 0; i <= markers.length; i++ ) {
        var position = new google.maps.LatLng(markers[i][1], markers[i][2]);
        
        bounds.extend(position);
        marker = new google.maps.Marker({
            position: position,
            map: map,
            title: markers[i][0]
        });
        
        // Add info window to marker    
       /* google.maps.event.addListener(marker, 'click', (function(marker, i) {
            return function() {
                infoWindow.setContent(infoWindowContent[i][0]);
                infoWindow.open(map, marker);
            }
        })(marker, i));*/

        // Center the map to fit all markers on the screen
        map.fitBounds(bounds);
    }
            
          }
      });

        

    // Set zoom level
    var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
        this.setZoom(14);
        google.maps.event.removeListener(boundsListener);
    });
}


window.initMap = initMap;
</script>

<?php
include 'footer.php';
?>