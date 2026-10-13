

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

