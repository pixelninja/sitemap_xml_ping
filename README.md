# Sitemap XML Ping

- Version: 1.1
- Author: Phill Gray
- Build Date: 2012-06-22
- Requirements: Symphony 2.3
- GitHub Repository: <http://github.com/pixelninja/sitemap_xml_ping>

## Installation

1. Upload the `sitemap_xml_ping` folder in this archive to your Symphony `extensions` folder.
2. Enable it by selecting the `Sitemap XML Ping` extension from System > Extensions, choose Enable from the With Selected menu, then click Apply.

Use this extension in conjunction with [Sitemap XML](http://symphony-cms.com/download/extensions/view/68689/). Once the sitemap has been generated, you can select the sections that they relate to in the preferences page and whenever a new entry is created or an existing entry is edited, the sitemap will get updated and ping Google/Bing automatically.

## Configuration

On the preferences page there will be a new section called `Sitemap XML Ping`. This is where you select the sections and input your authentication code. To retrieve this code, go to System -> Authors and either edit your own entry or create a new one specifically for this purpose. You need to check the `Allow remote login` checkbox and copy the random string at the end of the url (no slashes). Paste the token into the corresponding field in the preferences page.