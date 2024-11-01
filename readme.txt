=== Wordy for WordPress ===
Contributors: Wordy, slipfire
Tags: copy-editing, copyediting, wordpress, copy-editors, publishing, wordy, grammar, spelling, punctuation, structure, post-publish editing, freelance editors
Requires at least: 2.9.1
Tested up to: 3.1 RC2
Stable tag: 1.3.2

Wordy is the fastest, most reliable way of adding professional copy-editing to your WordPress blogs.

== Description ==

**[Wordy.com](http://www.wordy.com)** is the fastest, most reliable service for online copy editing on the Web. With Wordy for WordPress you can **seamlessly add professional editing to your WordPress publishing process**.

View a 2 minute walk-through of what [Wordy can do for you](http://www.youtube.com/watch?v=V3dBPu6AAw4&hd=1):

http://www.youtube.com/watch?v=V3dBPu6AAw4&hd=1

* Professional editors check your post for grammar, spelling, punctuation, and structure.
* Get a **FREE** instant quote the minute you start typing.
* Choose between US and UK English.
* **NO LOSS** of HTML formatting.

With Wordy for WordPress you can even publish your post first, and then send it to Wordy for post-publication editing.

**PUBLISH WITH CONFIDENCE**

Whether you deal with academic text, corporate literature, blogs or web content, in fact, any kind of copy, Wordy will help you publish with confidence. Wordy provides you with a standardised editing service in record breaking time at a very low cost.

1. Send your text seamlessly from WordPress. You instantly get a free price quote, and an approximate delivery time.

2. A Wordy editor will check your text for grammar, spelling, punctuation and structure. Wordy has editors that specialise in a wide range of subjects, which ensures your text will be of the very highest quality.

3. When you get your text back from Wordy, it is ready to publish. It's really as simple as that! Now, go publish with confidence!

== Installation ==

**Wordy requires PHP 5**

Use the built in installer and upgrader from within WordPress, or install Wordy for WordPress manually:

1. Use your FTP-program to upload the Wordy-directory to your wp-content/plugins directory
2. Activate Wordy through the 'Plugins' menu in WordPress
3. Go to 'Settings' to enable Wordy using a new or existing account
4. Configure language and publishing settings
5. Publish with confidence

If you upgrade manually simply repeat the installation steps and re-enable the plugin.

**If you upgrade automatically, please back up your database first!**

== Changelog ==

= 1.3.2 =
* Updated to work with WordPress 3.1 and jQuery 1.4.4.

= 1.3.1 =
* Added a system check for PHP 5

= 1.3.0 =
* Re-factored/reworked to be inline with the best practices for WordPress and PHP.
* Works with Wordy api version 2.
* Added Dashboard widget for quick access to Wordy Post status.
* New Plugin links for easy access to Settings and Support.

= 1.2.6 =
* Fixed conflict with OCMX framework and session_id. Minor fixes to user experience and user interface.

= 1.2.5 =
* Fixed compatibility issue with WordPress 3.0, in which the iframe for payment didn't pop up.

= 1.2.4 =
* Fixed directory lookup issue.

= 1.2.3 =
* Left-aligned sign-in form.

= 1.2.2 =
* Minimum cost to pay is now set at €3, instead of the previous €6.
* The 'additional settings' link on the settings page now directs to the proper page on wordy.com.

= 1.2.1 =
* Fixed major problem with previous release.

= 1.2 =
* Optimized the options page layout and initial login layout.
* Restructured the code, splitting out JS and CSS into their own files.
* Fixed the word counter.

= 1.1 =
* Replaced existing JS for opening pop-up window for Wordy payments with a system using Thickbox which comes bundled with WordPress. Implementation detailed below.
* Introduced 'WORDY_PLUGIN_URL' constant, containing the URL of the directory the Wordy plugin resides in, to call iframe.html into Thickbox.
* Removed 'remember to turn off pop-up blockers' message.
* Fixed wordcount/cost bug: The code would only fetch the wordcount from the visual editor, and otherwise fail. Now it fetches it regardless, and strips out HTML tags.
* Incremented the version to 1.1
* Changed the 'tested up to' from 2.9.1 to 2.9.2 in readme.txt.

To avoid hassling the user with having to worry about pop-up blocking, and to keep the Wordy plugin feeling integrated with WordPress, the plugin opens an iframe using Thickbox, a jQuery plugin that comes bundled with WordPress (introduced with, and for use mainly with the media manager) instead of in a pop-up winow. There are quite a few stumbling blocks in using Thickbox, it turned out, but I decided to stick with it for the reasons listed, and in doing so had to circumvent some of the native WP admin scripting, the details of which are as follows:

There are three major problems with WP's use of Thickbox:
* Thickbox is loaded with loadscript.php at the very end of the post pages, and Thickbox uses .ready(). We also use .ready(), but because we're loaded first, we're also run before Thickbox, and thus can't be sure that it'll be ready for us when we start to run.
* Thickbox doesn't leave behind much in the way of evidence of its own initialization.
* WP's media manager, idiotically, throws out Thickbox's resizing function (tb_position()) and puts its own into the window.resize event. And thirdly, 

To detect TB's initialization is done, we find the first a.thickbox element (the media manager icons), check its event object to see if Thickbox has touched it. If it has, we're good to go. This is done with wordy_checktb(), which is run ever 50ms on .ready(). To make sure the iframe opens even if the a.thickbox elements for some reason, we forceload it after 2s if it hasn't happened yet.

Secondly, to ensure that we can resize TB's window as we see fit, we similarly search window.resize's event object for WP's media managers tb_position() and replace it with our own (wordy_window_resize(), which we namespace 'resize.wordy' so we can remove it if we need to), which checks to see whether the open #TB_window belongs to Wordy. If it does, we run our own resize code.

Now all of that is out of the way, we load up Thickbox with iframe.html in an iframe. When iframe.html is ready(), it calls wordy_iframe_loaded() from its parent, which injects the Wordy login form into the iframe and the makes sure it's the right size. Meanwhile iframe.html is looking for the form, and when it sees it, it submits it, and we're off to wordy.com and greener pastures.

And that, as they say, is that.

= 1.0 =
* First version - basic but powerful