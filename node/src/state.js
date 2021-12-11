import "./utils/env.js";

export const state = {
    ticker: 1, // FLUX/USDT
    interval: null,
    startTime: 0,
    resetTime: 0,
    apiToken: null,
    threshold: process.env.APP_THRESHOLD,
    orderSize: process.env.ORDER_SIZE,
    orderDiff: process.env.ORDER_DIFF,
    buySellDiffBittrexToBinance: {},
    buySellDiffBinanceToBittrex: {},
    buySellOpBittrexToBinance: {
        'count' : 0,
        'order': {},
        'history': [],
    },
    buySellOpBinanceToBittrex: {
        'count' : 0,
        'order': {},
        'history': [],
    },
    sellBuyDiffBittrexToBinance: {},
    sellBuyDiffBinanceToBittrex: {},
    sellBuyOpBittrexToBinance: {
        'count' : 0,
        'order': {},
        'history': [],
    },
    sellBuyOpBinanceToBittrex: {
        'count' : 0,
        'order': {},
        'history': [],
    }
};