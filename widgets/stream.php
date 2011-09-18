<?php

/**
 * This is a widget with the twitter-stream
 * It will show do the oAuth-dance if no settings are stored. If the settings are stored it will grab the last
 *
 * @package		frontend
 * @subpackage	twitter
 *
 * @author		Tijs Verkoyen <tijs@sumocoders.com>
 * @since		2.6
 */
class FrontendTwitterWidgetStream extends FrontendBaseWidget
{
	/**
	 * The number of tweets to show
	 *
	 * @var	int
	 */
	const NUMBER_OF_TWEETS = 5;


	/**
	 * An array that will hold all tweets we grabbed from Twitter.
	 *
	 * @var	array
	 */
	private $tweets = array();


	/**
	 * Execute the extra
	 *
	 * @return	void
	 */
	public function execute()
	{
		// call parent
		parent::execute();

		// load template
		$this->loadTemplate();

		// parse
		$this->parse();
	}


	/**
	 * Get the data from Twitter
	 * This method is only executed if the template isn't cached
	 *
	 * @return	void
	 */
	private function getData()
	{
		// for now we only support one account, in a later stadium we could implement multiple account.
		$id = 1;

		// init the application-variabled, alter these keys if you want to user your own application
		$consumerKey = 'K99h85EM3twFkqoRwvWRKw';
		$consumerSecret = 'tVQa9QYJiJifhWkhRZaGKSIaf1Keb4WvmmFloa6ClY';

		// grab the oAuth-tokens from the settings
		$oAuthToken = FrontendModel::getModuleSetting('twitter', 'oauth_token_' . $id);
		$oAuthTokenSecret = FrontendModel::getModuleSetting('twitter', 'oauth_token_secret_' . $id);

		// grab the user if from the settings
		$userId = FrontendModel::getModuleSetting('twitter', 'user_id_' . $id);

		// require the Twitter-class
		require_once PATH_LIBRARY . '/external/twitter.php';

		// create instance
		$twitter = new Twitter($consumerKey, $consumerSecret);

		// if the tokens aren't available we will start the oAuth-dance
		if($oAuthToken == '' || $oAuthTokenSecret == '')
		{
			// build url to the current page
			$url = SITE_URL . '/' . $this->URL->getQueryString();
			$chunks = explode('?', $url, 2);
			$url = $chunks[0];

			// check if we are in th second part of the authorization
			if(isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']))
			{
				// get tokens
				$response = $twitter->oAuthAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);

				// store the tokens in the settings
				FrontendModel::setModuleSetting('twitter', 'oauth_token_' . $id, $response['oauth_token']);
				FrontendModel::setModuleSetting('twitter', 'oauth_token_secret_' . $id, $response['oauth_token_secret']);

				// store the user-id in the settings
				FrontendModel::setModuleSetting('twitter', 'user_id_' . $id, $response['user_id']);

				// redirect to the current page, at this point the oAuth-dance is finished
				SpoonHTTP::redirect($url);
			}

			// request a token
			$response = $twitter->oAuthRequestToken($url);

			// let the user authorize our widget
			$twitter->oAuthAuthorize($response['oauth_token']);
		}

		else
		{
			// set tokens
			$twitter->setOAuthToken($oAuthToken);
			$twitter->setOAuthTokenSecret($oAuthTokenSecret);

			// grab tweets
			$this->tweets = $twitter->statusesUserTimeline($userId, null);
		}
	}


	/**
	 * Parse
	 *
	 * @return	void
	 */
	private function parse()
	{
		// we will cache this widget for 10 minutes, so we don't have to call Twitter each time a visitor enters a page with this widget.
		$this->tpl->cache(FRONTEND_LANGUAGE . '_twitterWidgetStreamCache', (10 * 60 * 60));

		// if the widget isn't cached, we should grab the data from Twitter
		if(!$this->tpl->isCached(FRONTEND_LANGUAGE . '_twitterWidgetStreamCache'))
		{
			// get data from Twitter
			$this->getData();

			// init var
			$tweets = array();

			// build nice array which can be used in the template-engine
			foreach($this->tweets as $tweet)
			{
				// we don't want to show @replies, so skip tweets that have in_reply_to_user_id set.
				if($tweet['in_reply_to_user_id'] != '') continue;

				// init var
				$item = array();

				// add values we need
				$item['user_name'] = $tweet['user']['name'];
				$item['url'] = 'http://twitter.com/' . $tweet['user']['screen_name'] . '/status/' . $tweet['id'];
				$item['text'] = str_replace(array('<p>', '</p>'), '', FrontendTemplateModifiers::cleanupPlainText($tweet['text']));
				$item['created_at'] = strtotime($tweet['created_at']);

				// add the twee
				$tweets[] = $item;
			}

			// get the numbers
			$this->tpl->assign('widgetTwitterStream', array_slice($tweets, 0, self::NUMBER_OF_TWEETS));
		}
	}
}

?>