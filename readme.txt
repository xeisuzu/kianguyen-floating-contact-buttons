=== XeVN Floating Contact Buttons ===
Contributors: kianguyen
Donate link: https://xeisuzuvn.com/donate/
Tags: contact button, floating button, click to call, zalo, whatsapp
Requires at least: 5.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Floating call, Zalo, Messenger, WhatsApp, Telegram, Facebook, TikTok and YouTube buttons with full-color logos and GTM click tracking.

== Description ==

XeVN Floating Contact Buttons adds a small stack of contact buttons to the corner of your website so visitors can call, chat or find you in one tap.

**Button types**

* Call (tel:), SMS and email
* Zalo, Messenger, Viber, WhatsApp and Telegram
* Facebook, TikTok, YouTube and Instagram
* Directions (map link) and any custom link

**Customize each button**

* Four icon choices: full-color app-style platform logo (default), white logo on the button color, a simple icon, or your own image uploaded from the Media Library
* Your own label and color (brand colors are pre-filled)
* Show on all devices, mobile only or desktop only
* Optional attention effect: ringing icon, pulse ring or bounce
* Reorder, enable or disable buttons at any time (up to 12 buttons)

**Made for real visitors**

* Group buttons: show one "Contact us" button that opens the list (choose separately for phones and computers)
* Call button on computers: shows the phone number with a "Copy number" button instead of a tel: link computers cannot open
* Pre-filled messages for WhatsApp, SMS and email, with {title} and {url} placeholders for the current page
* Working hours with a weekly schedule (different hours per day, e.g. half-day Saturday): show a button only during or only outside working hours
* Page-specific button sets: e.g. the sales hotline on product pages and the workshop number on service pages, matched by page address, post/page ID or category
* Show the buttons right away, after the visitor scrolls, or after a few seconds

**Easy to set up**

* Phone / Computer switch on the settings page: each device shows only its own settings, with a live preview in the corner that updates as you edit (and can be hidden)
* Export and import settings as a .json file to copy them to another website

**Placement and style**

* Bottom right or bottom left, with adjustable distance, size and spacing, set separately for phones and computers
* On phones, optionally a horizontal bar at the bottom of the screen with labels under each icon and one larger "featured" button raised in the middle
* Round icons with hover tooltip, or icons with labels
* Hover colors are generated from your chosen color automatically
* Label text switches between white and dark automatically so it stays readable (WCAG contrast) on any button color

**Show only where you want**

* Hide on the front page, on all single posts of a post type, or on specific post/page IDs
* "Hide contact buttons on this page" checkbox in the post editor sidebar

**Click tracking for Google Tag Manager**

Each click pushes an event to `dataLayer` (default name `xevn_contact_click`) with `button_type`, `button_label`, `button_url`, `page_location` and `button_set` (the page-specific set name, or `default`) (copying the number in the desktop call box is sent with `button_type` = `phone_copy`), so you can build GA4 or Google Ads conversions in GTM. Optionally the same event is sent through `gtag()` if gtag.js is already on your page.

**Lightweight and cache friendly**

* No external requests, no CDN, no jQuery on the front end (logos are bundled as inline SVG)
* Assets load only on pages where buttons are shown
* Mobile/desktop rules use CSS media queries, so full-page caches (LiteSpeed Cache, WP Rocket, etc.) work correctly
* Respects visitors who prefer reduced motion

**Privacy**

The plugin does not collect, store or send any visitor data. Click tracking only adds an event to the `dataLayer` of your own page; what happens next depends on the tags you configure.

**Tiếng Việt**

Plugin tạo các nút liên hệ nổi (Gọi, Zalo, Messenger, Viber, WhatsApp, Telegram, Email, SMS, Chỉ đường, link tuỳ ý) ở góc màn hình. Có thêm nút Facebook, TikTok, YouTube, Instagram. Các nút mạng xã hội mặc định dùng logo màu giống icon app; có thể đổi sang logo trắng trên nền màu, biểu tượng đơn giản hoặc tải ảnh riêng lên. Mỗi nút chỉnh được màu, nhãn, hiển thị theo thiết bị và có hiệu ứng rung/sóng để gây chú ý. Có thể gom các nút thành một nút "Liên hệ", hiện số điện thoại kèm nút sao chép khi khách xem trên máy tính, soạn sẵn tin nhắn cho WhatsApp/SMS/Email, ẩn/hiện nút theo giờ làm việc, hiện nút sau khi cuộn trang hoặc sau vài giây. Trên điện thoại có thể chọn kiểu thanh ngang ở đáy màn hình, với một nút nổi bật nhô lên ở giữa. Có thể tạo bộ nút riêng cho từng nhóm trang (ví dụ trang xe tải dùng hotline bán hàng, trang dịch vụ dùng số xưởng) và đặt giờ làm việc khác nhau cho từng ngày. Trang cài đặt có công tắc Điện thoại / Máy tính: mỗi thiết bị có vị trí, khoảng cách mép, kích thước, khoảng cách giữa các nút và gom nhóm riêng, kèm xem trước trực tiếp; có xuất/nhập cài đặt để chép sang website khác. Có thể ẩn nút trên trang chủ, theo loại nội dung, theo ID hoặc ngay trong màn hình sửa bài. Mỗi lượt bấm được đẩy vào dataLayer để đo lường bằng Google Tag Manager. Vào **Cài đặt → Nút liên hệ**, mục **Ngôn ngữ hiển thị**, chọn **Tiếng Việt** để dùng giao diện tiếng Việt.

== Installation ==

1. Upload the `xevn-floating-contact-buttons` folder to `/wp-content/plugins/`, or install it from the Plugins screen.
2. Activate the plugin.
3. Go to **Settings → Contact Buttons**, enter your phone number, Zalo number or other contacts, and save.

== Frequently Asked Questions ==

= Why is a button not showing? =

A button is shown only when it is enabled and has a phone number, username or link. Also check its "Show on" setting, the mobile breakpoint, and the "Hide on" options.

= How do I track clicks in Google Tag Manager? =

Keep "Push a dataLayer event" on. In GTM, create a Custom Event trigger with the event name `xevn_contact_click` (or the name you set), add Data Layer Variables for `button_type`, `button_label` and `button_url`, then use them in your GA4 event or Google Ads conversion tag.

= How do I use different numbers on different pages? =

In Settings → Contact Buttons → Buttons, click "+ Add a set for specific pages". Give the set a name, enter the page addresses it applies to (for example /products/), post/page IDs or category slugs, then add its buttons or click "Copy buttons from Default" and change the numbers. Each page uses the first enabled set that matches; other pages use the Default buttons. The choice is made on the server for each page, so it works with page caching.

= Does the Messenger button work for personal profiles? =

Yes. Enter the username of your Facebook page or personal profile, or paste a full m.me link. For a personal profile, your Facebook privacy settings decide who can message you.

= How do working hours work? =

Turn on Settings → Contact Buttons → Working hours, set the opening hours for each day of the week, then set each button to "Only during working hours" or "Only outside working hours". The check uses your site time zone and runs in the visitor's browser, so it stays correct with page caching. If JavaScript is off, all buttons are shown.

= Which buttons support a pre-filled message? =

WhatsApp, SMS and email. Zalo and Messenger links do not support pre-filled text. Use {title} and {url} to insert the current page title and link.

= Can I use my own icons? =

Yes. Set the button's Icon to "Uploaded image", then upload or pick an image from the Media Library (or paste an image URL). In the round style the image fills the whole button like an app icon, so a square PNG or WebP of the platform's app icon works best.

= Does it work with caching plugins? =

Yes. The buttons are rendered in the page HTML and mobile/desktop rules use CSS only. There is no AJAX and no nonce on the front end.

= How do I hide the buttons on one page? =

Edit the page and tick "Hide contact buttons on this page" in the sidebar, or enter its ID in Settings → Contact Buttons → Hide on these IDs.

= How do I show the plugin in Vietnamese? / Làm sao dùng tiếng Việt? =

Go to Settings → Contact Buttons → Display language and choose "Tiếng Việt". "Same as website" uses community translations first and falls back to the built-in Vietnamese text when your site language is Vietnamese.

Vào Cài đặt → Nút liên hệ → Ngôn ngữ hiển thị, chọn "Tiếng Việt". Chế độ "Theo ngôn ngữ website" sẽ tự dùng tiếng Việt khi website đặt ngôn ngữ Tiếng Việt.

= Where do the platform logos come from? =

The logo shapes for Zalo, Messenger, Viber, WhatsApp, Telegram, Facebook, TikTok, YouTube and Instagram come from Simple Icons (CC0) and are bundled with the plugin. The plugin draws them in each brand's colors (app-style) or in white on the button color. They are updated with plugin releases. All logos are trademarks of their respective owners and are used only to identify each service; please follow each brand's own usage guidelines. Call, SMS, email, directions and link buttons use icons drawn for this plugin.

= How do I copy my settings to another website? =

Go to Settings → Contact Buttons → Export / import settings, click "Export settings" to download a .json file, then use "Import settings" on the other website. Importing replaces all settings there.

= Can phones and computers have different settings? =

Yes. At the top of Settings → Contact Buttons, switch between "Phone" and "Computer". Each device has its own position, distance from the edges, button size, spacing between buttons and grouping, and the preview in the corner follows the device you picked. Buttons, working hours, tracking and language are shared. When you update from an earlier build, the phone settings start with your current computer values.

= What does the phone bar look like? =

Switch to "Phone" at the top of Settings → Contact Buttons, then choose Style on phones → Layout → "Horizontal bar at the bottom of the screen". All buttons sit side by side with their labels; tick "Featured" on one button (for example Call) to make it larger and raised in the middle. The page gets extra space at the bottom so the bar does not cover content. Computers keep the floating buttons.

== Screenshots ==

1. Floating buttons with full-color platform logos and hover tooltip.
2. Icon with label style.
3. Settings page: buttons with icon choice (platform logo, simple icon or uploaded image).

== Third-party resources ==

* Simple Icons 16.36.0 - https://simpleicons.org - License: CC0 1.0 Universal (licenses/simple-icons-LICENSE.md). Used for the platform logo shapes in includes/brand-icons.php.

== Changelog ==

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.0.0 =
First release.
