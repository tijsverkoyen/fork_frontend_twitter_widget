{* We use the templates caching-feature as a way to limit the calls to the Twitter API *}
{cache:{$LANGUAGE}_twitterWidgetStreamCache}
	{* Only show the stream if there are tweets *}
	{option:widgetTwitterStream}
		<ul>
			{* Loop the tweets *}
			{iteration:widgetTwitterStream}
				<li>
					{* By using the cleanupplaintext-modifier the links in the tweet will be parsed. *}
					{$widgetTwitterStream.text|cleanupplaintext}

					<p class="date">
						<a href="{$widgetTwitterStream.url}">
							<time datetime="{$widgetTwitterStream.created_at|date:'Y-m-d\TH:i:s'}" pubdate>
								{$widgetTwitterStream.created_at|timeago}
							</time>
						</a>
					</p>
				</li>
			{/iteration:widgetTwitterStream}
		</ul>
	{/option:widgetTwitterStream}
{/cache:{$LANGUAGE}_twitterWidgetStreamCache}