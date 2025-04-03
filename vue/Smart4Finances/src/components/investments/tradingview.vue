<template>
    <div class="tradingview-widget-container">
      <div class="tradingview-widget-container__widget" ref="widgetContainer"></div>
      <div class="tradingview-widget-copyright">
        <a href="https://www.tradingview.com/" rel="noopener nofollow" target="_blank">
          <span class="blue-text">Track all markets on TradingView</span>
        </a>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    name: 'TradingViewWidget',
    mounted() {
      this.loadTradingViewWidget();
    },
    methods: {
      loadTradingViewWidget() {
        const script = document.createElement('script');
        script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-symbol-overview.js';
        script.async = true;
        script.innerHTML = JSON.stringify({
          "symbols": [
            ["BLACKBULL:US30|1D"],
            ["CAPITALCOM:US100|1D"],
            ["CAPITALCOM:US500|1D"],
            ["GBEBROKERS:JP225|1D"],
            ["CAPITALCOM:EU50|1D"],
            ["TVC:DXY|1D"],
            ["BITSTAMP:BTCUSD|1D"],
            ["GETTEX:AMZ|1D"],
            ["NASDAQ:MSFT|1D"],
            ["NASDAQ:AAPL|1D"]
          ],
          "chartOnly": false,
          "width": "100%",
          "height": 750,
          "locale": "en",
          "colorTheme": "ligth",
          "autosize": true,
          "showVolume": false,
          "showMA": false,
          "hideDateRanges": false,
          "hideMarketStatus": false,
          "hideSymbolLogo": false,
          "scalePosition": "right",
          "scaleMode": "Normal",
          "fontFamily": "-apple-system, BlinkMacSystemFont, Trebuchet MS, Roboto, Ubuntu, sans-serif",
          "fontSize": "10",
          "noTimeScale": false,
          "valuesTracking": "1",
          "changeMode": "price-and-percent",
          "chartType": "area",
          "maLineColor": "#2962FF",
          "maLineWidth": 1,
          "maLength": 9,
          "headerFontSize": "medium",
          "lineWidth": 2,
          "lineType": 0,
          "dateRanges": [
            "1d|1",
            "1m|30",
            "3m|60",
            "12m|1D",
            "60m|1W",
            "all|1M"
          ]
        });
  
        this.$refs.widgetContainer.appendChild(script);
      }
    }
  }
  </script>
  
  <style scoped>
  .tradingview-widget-container {
    width: 100%;
    height: 100%;
    min-height: 700px; /* Altura mínima recomendada */
  }
  </style>