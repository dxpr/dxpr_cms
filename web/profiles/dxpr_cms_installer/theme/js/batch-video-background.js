(function (Drupal, once) {
  Drupal.behaviors.dxprBatchVideoBackground = {
    attach: function (context, settings) {
      if (!document.body.classList.contains('install-page')) {
        return;
      }

      once('dxpr-batch-video-bg', 'body', context).forEach(function () {
        const themePath = settings.dxprCmsInstaller?.themePath;
        if (!themePath) return;
        
        const videoBackground = document.createElement('div');
        videoBackground.className = 'cms-installer__video-background';
        videoBackground.setAttribute('aria-hidden', 'true');
        
        const video = document.createElement('video');
        video.setAttribute('autoplay', '');
        video.setAttribute('muted', '');
        video.setAttribute('loop', '');
        video.setAttribute('playsinline', '');
        video.className = 'cms-installer__background-video';
        
        const source = document.createElement('source');
        source.src = themePath + '/videos/dxpr-bg-light-5_slowmo2x_bounce.webm';
        source.type = 'video/webm';
        
        video.appendChild(source);
        video.appendChild(document.createElement('div')).className = 'cms-installer__video-fallback';
        videoBackground.appendChild(video);
        
        document.body.insertBefore(videoBackground, document.body.firstChild);
      });
    }
  };
})(Drupal, once);