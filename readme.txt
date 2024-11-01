=== Watu to MailChimp ===
Contributors: prasunsen
Tags: exam, test, quiz, survey, wpmu, multisite, touch, mobile
Requires at least: 4.1
Tested up to: 5.8
Stable tag: 1.1
License: GPLv2 or later

A bridge between the Watu Quiz plugin and MailChimp 

/***

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>. 

***/

== Description ==

Automatically subscribe users who take [Watu Quiz](https://wordpress.org/plugins/watu/ "Watu Quiz") quizzes to your [MailChimp](http://mailchimp.com/ "MailChimp") mailing lists. You can choose which quiz to which mailing list connects and also restrict the subscription by the achieved grade.

The plugin allows you to avoid the double-optin email confirmation that's sent by default by MailChimp. Do this at your own risk: any abuse may cause your MailChimp account banned.

*Note that it may take few minutes until your subscribers appear in the list.* 

Once installed and activated the plugin you will see a link "Watu to MailChimp" under your Watu Quizzes menu.

Identical plugin is available for the Pro version of Watu Quiz.

== Installation ==

Install and activate like any regular WordPress plugin.

== Frequently Asked Questions ==

None yet, please ask in the forum.

== Screenshots ==

1. You can add rules for every quiz, and even multiple rules on the same quiz - so when different grade is achieved, the user is assigned to a different mailing list.

== Changelog ==

= Version 1.1 =
- Moved Watu to Mailchimp menu under Watu Quizzes menu
- Replaced CURL usage with wp_remote_post and wp_remote_get
- Do not add user to Mailchimp if they are already subscribed
- Switched the MailChimp API to 3.0

= Version 0.9 =

First public release.