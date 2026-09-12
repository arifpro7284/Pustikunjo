(function ($) {
    let socialStory;
    $(document).ready(function ($) {
        let playlist = []
        stories_data.stories.forEach(function (story) {
            let obj = {};
            obj.title = stories_data.account.username;
            obj.date = timeDifference(Date.now(), new Date(story.timestamp).getTime());
            obj.url = story.media_url
            obj.icon = stories_data.account.profile_picture_url
            playlist.push(obj)
        });

        socialStory = new Story({
            playlist: playlist
        });

        jQuery('.wis-avatar-stories').on('click', function (e) {
            e.preventDefault();
            socialStory.launch();
        });
        jQuery('.story-close').on('click', function (e) {
            e.preventDefault();
            socialStory.close();
        });
    });

    function timeDifference(current, previous) {
        const msPerMinute = 60 * 1000;
        const msPerHour = msPerMinute * 60;
        const msPerDay = msPerHour * 24;
        const msPerMonth = msPerDay * 30;
        const msPerYear = msPerDay * 365;

        const elapsed = current - previous;

        if (elapsed < msPerMinute) {
            return Math.round(elapsed / 1000) + ' seconds ago';
        } else if (elapsed < msPerHour) {
            return Math.round(elapsed / msPerMinute) + ' minutes ago';
        } else if (elapsed < msPerDay) {
            return Math.round(elapsed / msPerHour) + ' hours ago';
        } else if (elapsed < msPerMonth) {
            return 'approximately ' + Math.round(elapsed / msPerDay) + ' days ago';
        } else if (elapsed < msPerYear) {
            return 'approximately ' + Math.round(elapsed / msPerMonth) + ' months ago';
        } else {
            return 'approximately ' + Math.round(elapsed / msPerYear) + ' years ago';
        }
    }
})(jQuery);
