<?php

class SpecialImportFromEtherpad extends SpecialPage {

	private $errors = array();
	private $formErrors = array();

	public function __construct() {
		global $wgImportFromEtherpadSettings;
		parent::__construct('ImportFromEtherpad', 'createpage');
		$this->pathToPandoc = $wgImportFromEtherpadSettings->pathToPandoc;
		$this->pandocCmd = $wgImportFromEtherpadSettings->pandocCmd;
		$out = $this->getOutput();
		$out->addHTML('<div style="border: 1px solid black; padding: 5px; background: orange; font-weight: bold; font-size: 1.2em;">Thank you for helping to test this extension. Please report any issues <a href="https://github.com/christi3k/ImportFromEtherpad/issues">on Github</a>. If you have quesetions, feel free to ask the developer (<a href="https://mozillians.org/u/ckoehler/">ckoehler</a>) via irc or email.</div>');
	}

	function execute( $par ) {
		// @todo verify this is in the right place and used correctly
		$this->checkReadOnly();

		$this->setHeaders();
		$this->outputHeader();

		$out = $this->getOutput();

		// allow only users with create and edit permissons to access
		$user = $this->getUser();
		if ( !$user->isAllowedAny( 'createpage', 'edit' ) ) {
			throw new PermissionsError( 'createpage' );
		}

		$out->addWikiMsg('importfrometherpad-intro');
		$request = $this->getRequest();
		// if formsubmitted, process the request
		if ($request->wasPosted() && $request->getVal('action') == 'submit') {
			$this->loadRequest();
		}
		else {
			$this->displayForm();
		}
		// either way display the form
		// if unprocessed, basic form will be shown
		// otherwise will display with errors and/or result of import
	}

	function getGroupName() {
		return 'pagetools';
	}

	private function displayForm( $errors = array() ) {
		$message = '';
		$action = $this->getPageTitle()->getLocalURL(array('action'=>'submit'));
		$out = $this->getOutput();
		$user = $this->getUser();

		$request = $this->getRequest();

		// get values from request object
		$this->etherpadLink= $request->getText('etherpadLink');
		$this->targetpageTitle = $request->getText('targetpageTitle');
		$this->targetpageNs = $request->getIntOrNull('targetpageNs');

		if ( count ( $errors ) == 1 && isset ( $errors[0][0] ) && $errors[0][0] == 'targetpage-exists') {
				// add radio buttons to prompt for replace or append
				$appendOrReplaceRadio = "<tr><td colspan='2'><strong>".$this->msg('importfrometherpad-append-or-replace-label')."</strong></td></tr>";
				$appendOrReplaceRadio .= "<tr><td></td>";
				$appendOrReplaceRadio .= "<td class='mw-submit'>";
				$appendOrReplaceRadio .= Xml::radioLabel($this->msg('importfrometherpad-append-btn')->text(), 'pageAppendOrReplace', 'append', 'mw-append');
				$appendOrReplaceRadio .= Xml::radioLabel($this->msg('importfrometherpad-replace-btn')->text(), 'pageAppendOrReplace', 'replace', 'mw-replace');
				$appendOrReplaceRadio .= "</td></tr>";
				$errors = array();
		} else {
				$appendOrReplaceRadio = '';
		}

		// display all errors
		if( count ($errors) > 0 ){
			foreach($errors as $error) {
				$out->addHTML('<div class="error">'.$this->msg($error)->text().'</div>');
			}
		}

		if ( $user->isAllowed( 'createpage' ) ) {
			$out->addHTML(
				Xml::fieldset($this->msg('importfrometherpad-fieldset-legend')->text()) .
				Xml::openElement(
					'form', array(
						'method' => 'post',
						'action' => $action,
						'id' => 'importfrometherpad-form'
					)
				) .
				$this->msg('importfrometherpad-text')->parseAsBlock() .
				Html::hidden('action', 'submit') .
				Xml::openElement('table',array('id'=>'importfrometherpad-table')) .
				"<tr><td class='mw-label'>" .
				Xml::label($this->msg('importfrometherpad-label-eplink')->text(), 'mw-eplink') .
				"</td>" .
				"<td class='mw-input'>" .
				Xml::input('etherpadLink', 50, ($this->etherpadLink), array('id' => 'mw-eplink', 'type'=>'text')) .
				"</td></tr>" .
				"<tr><td class='mw-label'>" .
				Xml::label($this->msg('importfrometherpad-label-targetpage')->text(), 'mw-targetpage') .
				"</td>" .
				"<td class='mw-input'>" .
				Html::namespaceSelector(
					array(
						'selected' => ($this->targetpageNs ? $this->targetpageNs : NS_MAIN)
					),
					array('name' => 'targetpageNs', 'id' => 'mw-targetpage-ns')
				) .
				Xml::input('targetpageTitle', 50, $this->targetpageTitle, array('id' => 'mw-targetpage', 'type'=>'text')) .
				"</td></tr>" .
				$appendOrReplaceRadio .
				"<tr><td></td>" .
				"<td class='mw-submit'>" .
				Xml::submitButton($this->msg('importfrometherpad-submitbtn')->text(), array('id' => 'importfrometherpad-submit')) .
				"</td></tr>" .
				Xml::closeElement('table') . 
				Html::hidden( 'editToken', $user->getEditToken() ) .
				Xml::closeElement('form') . 
				Xml::closeElement('fieldset')
			);
		} else {
			$out->addWikiMsg('importfrometherpad-nopermission');
		}
	}

	protected function loadRequest() {
		$request = $this->getRequest();
		$user = $this->getUser();

		// get values from request object
		// @todo make this an array so we can iterate on it
		$this->etherpadLink= $request->getText('etherpadLink');
		$this->targetpageTitle = $request->getText('targetpageTitle');
		$this->targetpageNs = $request->getIntOrNull('targetpageNs');
		$this->pageAppendOrReplace = $request->getVal('pageAppendOrReplace');

		// grab output object
		// https://doc.wikimedia.org/mediawiki-core/REL1_23/php/html/classSpecialPage.html#a1dd08360c4383ac5aff17107da7b2cd5
		$output = $this->getOutput();

		// initiate status object
		// use built-in status tracking 
		// https://doc.wikimedia.org/mediawiki-core/master/php/html/classStatus.html
		$this->result = new Status;

		// check edit token
		$this->token = $user->getEditToken();
		if ( !$user->matchEditToken( $request->getVal( 'editToken' ) ) ) {
			$this->result->fatal('import-token-mismatch');
		} 
		
		//check permissions
		if ( !$user->isAllowed( 'createpage' ) ) {
			throw new PermissionsError( 'createpage' );
		}

		// initiate exception var
		$exception = false;

		// try the import and catch any exceptions
		try {
			$importResult = $this->importEtherpad();
		} catch ( MWException $e ) {
			$exception = $e;
		}

		// now format output, starting with exceptions
		if ( $exception ) {
			// @todo use our own messages for this?
			$output->wrapWikiMsg(
				"<p class=\"error\">\n$1\n</p>",
				array( 'importfailed', $exception->getMessage() )
			);
		} elseif ( !$this->result->isGood() ) {
			//show any fatal errors that are not exceptions
			// @todo use our own messages for this?
			$output->wrapWikiMsg(
				"<p class=\"error\">\n$1\n</p>",
				array( 'importfailed', $this->result->getWikiText() )
			);
			//$this->displayForm();
		} else if ( !$importResult) {
			//$this->displayForm($this->formErrors);
		} else {
			// show success!
			$output->addWikiMsg( 'importfrometherpad-importsuccess' );
			if (isset($this->resultMessage)) {
				$output->addWikiMsg( $this->resultMessage );
			}
			$newLink = Linker::linkKnown($this->newTitle);
			$output->addHTML( $this->msg( 'importfrometherpad-newlink' )->rawParams( $newLink )->parseAsBlock() );

			// now clear request vars so form is re-displayed without previous input
			$request->unsetVal('etherpadLink');
			$request->unsetVal('targetpageTitle');
			$request->unsetVal('targetpageNs');
			$request->unsetVal('pageAppendOrReplace');

			// reset form errors array
			$this->formErrors = array();
		}
		//$output->addHTML( '<hr />' );

		// always re-display form after loading request
		// if there are errors or other messages, form will show them
		$this->displayForm($this->formErrors);
	}

	private function importEtherpad() {
		// check validity of ep url
		// right now this just checks to make sure it's a valid URI
		// @todo investigate if there is a way to check for valid ep instance
		if ( !Http::isValidURI($this->etherpadLink) ) {
			$this->result->fatal('importfrometherpad-invalidetherpad');
			return false;
		}

		// check validity of targettitle
		// @todo check permissions if attempting to use namespaces?
		$this->newTitle = Title::makeTitleSafe($this->targetpageNs, $this->targetpageTitle);
		if ( is_null($this->newTitle) ) {
			$this->result->fatal( 'importfrometherpad-invalidpagetitle' );
			return false;
		}

		// does the target page already exist?
		// and has the user not already indicated we should append/or replace?
		if ( $this->newTitle->exists() && !isset($this->pageAppendOrReplace) ) {
			$this->formErrors = array( array( 'targetpage-exists' ) );
			return false;
		}

		// convert content
		// all the work of Pandoc converting from html to wikimarkup is here
		if ( !$this->convertContent() ) {
			$this->result->fatal( 'importfrometherpad-fail' );
			return false;
		}

		// save article
		$apiResult = $this->saveArticle();

		// now check results of save and set result message and return value accordingly
		if ( isset( $apiResult['edit'] ) && $apiResult['edit']['result'] == 'Success' ){
			if ( isset( $apiResult['edit']['new'] ) ) {
				$this->resultMessage = 'importfrometherpad-sucessful-new';
			}
			else if ( isset( $apiResult['edit']['oldrevid'] ) && $apiResult['edit']['oldrevid'] == 0 ) {
				$this->resultMessage = 'importfrometherpad-sucessful-update';
			}
			else if ( isset( $apiResult['edit']['nochange'] ) ) {
				$this->resultMessage = 'importfrometherpad-sucessful-nochange';
			}
			$this->result->setResult(true);
			return true;
		} else {
			$this->result->fatal( 'importfrometherpad-savefail' );
			return false;
		}
	}

	private function saveArticle() {
		$textOrAppendText = ( isset( $this->pageAppendOrReplace ) && $this->pageAppendOrReplace == 'append') ? 'appendtext' : 'text';
		// action for both page edit and create is 'edit'
		// https://www.mediawiki.org/wiki/API:Edit
		$action = 'edit';
		// @todo localize comment text, including link to specialpage?
		$comment = 'Page generated from '. $this->etherpadLink . ' by [[Special:ImportFromEtherpad]]';
        $api = new ApiMain(
                new DerivativeRequest(
                $this->getRequest(), // Fallback upon $wgRequest if you can't access context
                array(
            'action' => $action,
            'title' => $this->newTitle,
            $textOrAppendText => $this->content, // can only use one of 'text' or 'appendtext'
            'summary' => $comment,
            'notminor' => true,
            'token' => $this->token
                ), true // was posted?
                ), true // enable write?
        );
		$api->execute(); // actually save the article.
		$apiResult = $api->getResult()->getData();
		// get and return apiResult object
		return $api->getResult()->getData();
	}

	private function convertContent()
	{
		global $wgImportFromEtherpadSettings;

		// derive the export url from etherpad url
		$exportUrl = $this->getExportUrl();
		if ($exportUrl === false) {
			wfDebug('no exportUrl, aborting');
			return false;
		}

		if ( $exportUrl['scheme'] == 'lite-mediawiki' ) {
			// already mediawiki so no need to run through pandoc,
			// just go get it
			wfDebug('this is eplite that supports mediawiki export, using that');
			$this->content = $this->fetchContent( $exportUrl['url'] );
			if ( !$this->content) { return false; }
		}
		else {
			wfDebug('converting with pandoc');
			// @todo add check that pandoc exists
			$panDocCmd = $this->pathToPandoc . $this->pandocCmd . " -f html -t mediawiki " . $exportUrl['url'];
			$this->content = wfShellExec($panDocCmd, $returnVal);
			wfDebug('Pandoc return value: ' . $returnVal);
			if ( $returnVal !== 0 ) {
				$this->formErrors[] = array( 'importfrometherpad-pandocerror' );
				return false;
			}
			// @todo should prob move to a helper function
			if ( isset($wgImportFromEtherpadSettings->contentRegexs) ) {
				foreach ($wgImportFromEtherpadSettings->contentRegexs as $regex) {
					$this->content = preg_replace("/{$regex[0]}/m", "{$regex[1]}", $this->content);
				}
			}
		}
		return true;
	}

	private function getExportUrl()
	{
		wfDebug('attempting to determine export url');
		$parsedUrl = parse_url($this->etherpadLink);
		// build an array of possible valid etherpad export urls
		// in order of preference
		$schemes = array();
		$schemes['classic-html'] = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . '/' . 'ep/pad/export' . $parsedUrl['path'] . '/latest?format=html';
		$schemes['lite-mediawiki'] = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '/export/mediawiki';
		$schemes['lite-html'] = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'] . '/export/html';

		// now loop through them until we find a good one
		foreach ($schemes as $scheme => $url) {
			wfDebug('testing: ' . $url);
			if ( $this->isGoodExportUrl( $url ) ) {
				return array('scheme' => $scheme, 'url' => $url);
			}
		}
		// if we get this far and don't have a valid url, return false
		$this->formErrors[] = array( 'importfrometherpad-novalidexporturl' );
		return false;
	}

	private function isGoodExportUrl( $url ) {
		$req = MWHttpRequest::factory( $url );
		$status = $req->execute();
		if ( $status->isOK() ) {
			wfDebug('url ' . $url . ' is OKAY');
			return true;
		}
		else {
			$statusCode = $req->getStatus();
			wfDebug('Response code for etherpad url ' . $url . ' returned ' . $statusCode);
			return false;
		}
	}

	private function fetchContent( $url ) {
		$req = MWHttpRequest::factory( $url );
		$status = $req->execute();
		if ( $status->isOK() ) {
			return $req->getContent();
		}
		else {
			$statusCode = $req->getStatus();
			wfDebug('Response code for etherpad url ' . $url . ' returned ' . $statusCode);
			return false;
		}
	}

}


/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
