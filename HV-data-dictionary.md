# HomeValue Wholesale: transaction data dictionary

Prepared for Spartera evaluation. Field names match the template Scott sent.

## Dataset summary

| | |
|---|---|
| Grain | one row per line item on an order |
| Rows | 36,571 |
| Orders | 1,804 |
| Distinct SKUs | 2,779 |
| Distinct buyers | 299 |
| Date range | 2024-05-13 to 2026-08-24 |
| Geography | United States |
| Refresh | daily |
| Business | B2B wholesale, general merchandise and food staples sold to independent grocers and markets |

## Fields

Fill rates are measured per row, across the whole dataset.

| Field | Type | Fill | Notes |
|---|---|---|---|
| transaction_id | string | 100% | Order identifier. Repeats across rows of the same order. |
| transaction_date | date | 100% | Order date. |
| sku_id | string | 100% | Internal SKU. Stable over time. |
| product_name | string | 99.9% | Free-text description, includes pack size. |
| product_category | string | 71.3% | 28 categories, for example Cookware, Glassware, Oriental Authentic Food & Drink. Uncategorized rows are mostly aluminum trays and cookware. |
| quantity | integer | 100% | Units on the line. |
| unit_sale_price | decimal | 100% | Price per unit charged, USD. |
| unit_wholesale_cost | decimal | 88% | Our cost per unit, USD. See caveat below. |
| purchasing_business_id | string, hashed | 100% | SHA-256, salted. Stable across rows so repeat-buyer behavior is analyzable. |
| purchasing_business_type | string | 100% | **Not usable.** Only two values present: Retailer (36,466) and Restaurant (105). Effectively a constant. |
| geography_state | string | 94.2% | US state. 333 rows have a malformed value (a ZIP in the state field) and need cleaning. |
| geography_zip | string | 85.2% | Buyer ZIP. |

## Caveats I want on the table up front

1. **unit_wholesale_cost is current cost, not cost at time of sale.** It is joined from the product catalogue, not stamped on the historical transaction. Fine for current margin analysis, not valid for historical margin trending.

2. **purchasing_business_type is not real.** The template asks for restaurant/retailer/distributor. We only have two values and 99.7% are one of them. Buyer segmentation would have to be inferred from business name or enriched externally. I do not want to represent this as available.

3. **Two source systems.** The line-item detail above comes from our ordering platform. Our accounting system is separate and its invoiced sales totals do not reconcile with order totals from the ordering platform. For a product priced on transaction counts and SKU velocity this is workable, but any product that quotes revenue needs to be built off the accounting system instead.

4. **Additional fields available if useful:** order-level payment terms, payment status, delivery status, ordered vs delivered quantity, customer group, and credit or return documents (tracked separately from sales).

## Sample rows

See attached HV-sample-rows.csv. 14 randomly selected rows, buyer IDs hashed.
