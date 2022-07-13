/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// start the Stimulus application
import './bootstrap';
import 'select2';

$(document).ready(function () {
    $('.form-select').select2();
    $('#export').on('click', function(e){
        $("#tableexport").table2excel({
            exclude: ".noExport",
            name: "Data",
            filename: "resultatElection",
        });
    });
});

const dash_link_href = location.href;
console.log(dash_link_href);
const link_dash = document.querySelectorAll('.nav-link');
for (let i = 0; i < link_dash.length; i++) {
    const element = link_dash[i];
    if (element.href === dash_link_href) {
        element.className = "active"
    }
    
}

