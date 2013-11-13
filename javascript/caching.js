// This scripts displays the off-line storage caching  state of the web page.
// It is used to give an indication of what is / has changed on the page.


var cacheStatusValues = [];
cacheStatusValues[0] = 'uncached';
cacheStatusValues[1] = 'idle';
cacheStatusValues[2] = 'checking';
cacheStatusValues[3] = 'downloading';
cacheStatusValues[4] = 'updateready';
cacheStatusValues[5] = 'obsolete';

var cache = window.applicationCache;
cache.addEventListener('cached', logEvent, false);
cache.addEventListener('checking', logEvent, false);
cache.addEventListener('downloading', logEvent, false);
cache.addEventListener('error', logEvent, false);
cache.addEventListener('noupdate', logEvent, false);
cache.addEventListener('obsolete', logEvent, false);
cache.addEventListener('progress', logEvent, false);
cache.addEventListener('updateready', logEvent, false);

function logEvent(e) {
    var message = "";
    switch (e.type) {
        case "checking":
            message = "Checking for updates.";
            break;
        case "noupdate":
            message = "No new updates available.";
            break;
        case "downloading":
            message = "Downloading updates.";
            console.log(e.toString());
            break;
        case "cached":
            message = "Downloading complete for all resources.";
            break;
        case "updateready":
            cache.swapCache();
            window.location.reload();

            break;
        case "obsolete":
            message = "error retriving manifest.";
            break;
        case "error":
            message = "error with the manifest.";
            break;
        default:
            message = "Downloaded " + e.loaded + " of " + e.total + " files";
    }
    message = message + '<br/>';
    $("#CacheState").append(message);
    console.log(message);
}

window.applicationCache.addEventListener(
    'updateready',
    function () {
        window.applicationCache.swapCache();
        console.log('swap cache has been called');
    },
    false
);
