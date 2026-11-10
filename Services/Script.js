const MDBLOG_BASE_URL = "<?= ApiTokenDetails::backEndBaseUrl(); ?>"; 
const MDBLOG_getTokenName = "<?= ApiTokenDetails::tokenName(); ?>";

const MDBLOG_LW_TOKEN = localStorage.getItem(`${MDBLOG_getTokenName}`); 


function timeAgo(timestamp) {
    // Convert MySQL datetime to a format JavaScript handles consistently
    const timeAgo = new Date(timestamp.replace(" ", "T")).getTime();
    const currentTime = Date.now();

    const seconds = Math.floor((currentTime - timeAgo) / 1000);

    const minutes = 60;
    const hours = 3600;
    const days = 86400;
    const weeks = 604800;
    const months = 2629440;
    const years = 31553280;

    if (seconds < minutes) {
        return `${seconds} second${seconds > 1 ? "s" : ""} ago`;

    } else if (seconds < hours) {
        const value = Math.floor(seconds / minutes);
        return `${value} minute${value > 1 ? "s" : ""} ago`;

    } else if (seconds < days) {
        const value = Math.floor(seconds / hours);
        return `${value} hour${value > 1 ? "s" : ""} ago`;

    } else if (seconds < weeks) {
        const value = Math.floor(seconds / days);
        return `${value} day${value > 1 ? "s" : ""}`;

    } else if (seconds < months) {
        const value = Math.floor(seconds / weeks);
        return `${value} week${value > 1 ? "s" : ""} ago`;

    } else if (seconds < years) {
        const value = Math.floor(seconds / months);
        return `${value} month${value > 1 ? "s" : ""} ago`;

    } else {
        const value = Math.floor(seconds / years);
        return `${value} year${value > 1 ? "s" : ""} ago`;
    }
}



 let mdblog_loaderCount = 0;
function mdblog_fx_lifetech_button_loader_open() {
  mdblog_loaderCount++;

  let loaderEl = document.querySelector('.lifetech_button_loader');
  let overlayEl = document.querySelector('.lifetech_page_overlay');

  if (!overlayEl) {
    overlayEl = document.createElement('div');
    overlayEl.className = 'lifetech_page_overlay';
    document.body.appendChild(overlayEl);
  }

  if (!loaderEl) {
    loaderEl = document.createElement('div');
    loaderEl.className = 'lifetech_button_loader';
    document.body.appendChild(loaderEl);
  }

  overlayEl.style.display = 'block';
  loaderEl.style.display = 'block';
}

function mdblog_fx_lifetech_button_loader_close() {
  mdblog_loaderCount--;

  if (mdblog_loaderCount <= 0) {
    mdblog_loaderCount = 0;

    const loaderEl = document.querySelector('.lifetech_button_loader');
    const overlayEl = document.querySelector('.lifetech_page_overlay');

    if (loaderEl) loaderEl.style.display = 'none';
    if (overlayEl) overlayEl.style.display = 'none';
  }
}

function mdblogFormatTime(timeString, includeSeconds = false) {
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };
    
    if (includeSeconds) {
        options.second = '2-digit';
    }
    
    return new Date(timeString).toLocaleString('en-US', options);
}    
  

