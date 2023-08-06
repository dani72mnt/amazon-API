import scrapy
from scrapy.exporters import JsonLinesItemExporter

class TestSpider(scrapy.Spider):
    name = "test_spider"

    def start_requests(self):
        start_url = 'https://erythrogen.com/en/'
        yield scrapy.Request(start_url, callback=self.parse)

    def parse(self, response):
        image_selector = response.css('.navbar-brand img.logo')
        data = {
            'image': image_selector.css('::attr(src)').get(),
            'title': response.css('.home-header-content h4::text').get(),
        }

        with open('scraped_data.json', 'ab') as f:
            exporter = JsonLinesItemExporter(f)

            # Start exporting data
            exporter.start_exporting()

            # Export the data to the JSON file
            exporter.export_item(data)

            # Finish exporting
            exporter.finish_exporting()

        yield data
