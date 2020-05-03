## 策略分析

```bash
$ tactics:run {date} {tactics}
```

| 欄位    | 解釋       |
| ------- | ---------- |
| date    | 日期 or 年 |
| tactics | 策略       |



策略

| name                             | 解釋               |
| -------------------------------- | ------------------ |
| BreakMonthMa                     | 突破月線           |
| TrustBuyBreakMonthMa             | 突破月線且投信買超 |
| ForeignInvestmentBuyBreakMonthMa | 突破月線且外資買超 |



## 策略盈虧分析

```bash
$ tactics:profit {date} {tactics} {profit}
```

| 欄位    | 解釋       |
| ------- | ---------- |
| date    | 日期 or 年 |
| tactics | 策略       |
| profit  | 買賣點策略 |



買賣點策略

| name                          | 解釋                      |
| ----------------------------- | ------------------------- |
| buy_next_day_close_three_sell | 買隔日收盤價 再三天後賣出 |
|                               |                           |
|                               |                           |

