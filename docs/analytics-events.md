# Analytics events

The plugin sends a small set of events to Google Analytics 4 so the site's enquiries and case-study reads can be measured. The events are written in code (not Google Tag Manager), so every change is reviewed and version controlled.

They are sent through the `gtag()` function that Site Kit already loads. If `gtag()` is not available (for example for logged-in users, whom Site Kit does not track, or when a visitor has not given consent), nothing is sent.

No form field values are ever read or sent, so no personal data leaves the site.

## Events

| Event | When it is sent | Conversion in GA4? |
| --- | --- | --- |
| `consultation_submit` | The Free Consultation form is submitted. This is the primary conversion. | Yes |
| `contact_submit` | The Contact form is submitted. This is the secondary conversion. | Yes |
| `website_brief_submit` | The Website Brief form is submitted. | Yes |
| `portfolio_read` | A visitor reads a case study (see below). | Yes |
| `consultation_cta_click` | A consultation CTA is clicked. | No (supporting event) |
| `newsletter_subscribe` | A Newsletter form is submitted. | No (supporting event) |

Marking an event as a conversion (key event) is done in the Google Analytics interface, not in the plugin.

### Parameters

| Event | Parameters |
| --- | --- |
| Form events | `form_name`: `free_consultation`, `contact`, `website_brief` or `newsletter` |
| `consultation_cta_click` | `cta_location`: `header`, `footer`, `hero`, `popup` or `page`. `cta_text`: the button text, up to 60 characters. |
| `portfolio_read` | `project`: the case study's slug. `read_trigger`: `time` or `scroll`. |

## How each event is detected

- **Forms that show a confirmation message** (Website Brief): the forms on this site submit with a normal page reload, so the event is sent when the page loads with Gravity Forms' confirmation message on it. A "form saved" notice (Save and Continue) does not count. Forms set to submit with AJAX are covered too, through the Gravity Forms confirmation event.
- **Forms that redirect to a thank-you page** (Free Consultation, Contact and Newsletter): the form leaves the page, so the event is sent when the thank-you page loads. Reloading the thank-you page does not send it again for 30 minutes in the same browser tab.
- **Consultation CTA**: a click on an element with the Popup Maker trigger class `pum-triggercta`, or on a link to the Free Consultation page.
- **Portfolio read**: on a single case study (the `project` post type), the event is sent once per page view after about 30 seconds with the tab visible, or when half the page has been scrolled, whichever comes first.

Forms are matched by title (for example "Free Consultation"), so the tracking works on every environment even though Gravity Forms form IDs differ between sites. If a form is renamed, update the `ls_plugin_analytics_form_events` filter.

## Checking the events

- Turn on `WP_DEBUG`: each event is also written to the browser console as `[ls-plugin analytics]`.
- In Google Analytics, open Admin, then DebugView, or the Realtime report. Test as a logged-out visitor, because Site Kit does not track logged-in users.
- Put TEST in the message of any test form submission, and delete the test entries afterwards.

## Switching it off or changing it

| Filter | What it does |
| --- | --- |
| `ls_plugin_analytics_events_enabled` | Return `false` to switch all event tracking off. |
| `ls_plugin_analytics_form_events` | Form titles and the event each one sends. |
| `ls_plugin_analytics_thank_you_pages` | Thank-you pages and the event each one sends. |
| `ls_plugin_analytics_config` | The full settings passed to the script. |

The source is `src/js/analytics-events.js` and `inc/class-analytics-events.php`. Run `npm run build` after changing the script.
