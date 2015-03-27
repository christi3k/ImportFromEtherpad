/**
 * JavaScript for Welcome in Example.
 * Inserts a welcome message into the page content.
 */
( function ( mw, $ ) {
	var suggestTitle;

  suggestTitle = {
    init: function () {
      allConfig = mw.config.get( 'wgImportFromEtherpadSettings' );

      $( '#mw-eplink' ).focusout( function() {
        pattern = /(\w+):\/\/([\w.]+)\/(\S*)/;
        eplink = $( '#mw-eplink' ).val();
        result = eplink.match(pattern);
        if (result != null) {
          ephost = result[2];
          eppath = result[3];
        }

        if (allConfig.hostRegexs != null) {
          for (var i = 0; i < allConfig.hostRegexs.length; i++) {
            ephost = ephost.replace(new RegExp(allConfig.hostRegexs[i][0],"g"), allConfig.hostRegexs[i][1]);
          }
        }

        if (allConfig.pathRegexs != null) {
          for (var i = 0; i < allConfig.pathRegexs.length; i++) {
            eppath = eppath.replace(new RegExp(allConfig.pathRegexs[i][0],"g"), allConfig.pathRegexs[i][1]);
          }
        }

        suggested = ephost + eppath
      
        $( '#mw-targetpage' ).val(suggested);
      });
    }
  }

	mw.libs.suggestTitle = suggestTitle;

}( mediaWiki, jQuery ) );
