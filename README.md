
# P6rguP6der Euro Converter
A simple lightweight WordPress plugin for converting Euros into other currencies (and vice versa).

It is meant to be used as a client code inside themes or other plugins.

For example:

```
$conv = new P6rguP6derEuroConverter();

// Convert 200 USD into Euros
$sum_usd = 200.00;
$sum_eur = $conv->convert_into_EUR($sum_usd, 'USD');
```

