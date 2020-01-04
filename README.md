# AuroraExtensions\_SimpleReturns

Self-service RMA functionality for Magento Open Source.

## Table of Contents

+ [Description](#description)
+ [Features](#features)
  - [Customer](#customer)
  - [Administrator](#administrator)
  - [Developer](#developer)
+ [Installation](#installation)
+ [Notes](#notes)
+ [Documentation](https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/latest/)
+ [Roadmap](https://github.com/auroraextensions/simplereturns/wiki/Roadmap)
+ [Sample Data](https://github.com/auroraextensions/simplereturns-sampledata)
+ [Simple Returns Pro](https://auroraextensions.com/products/simple-returns-pro)

## Description

Simple Returns provides enhanced RMA functionality intended for Magento Open Source.
It extends the default RMA capabilities built into Magento Open Source<sup>1</sup>,
and offers a range of additional features to improve the RMA experience for both
customers and administrators alike.

## Features

Simple Returns is packed with features that can substantially improve an RMA pipeline.
The default RMA functionality provided by Magento Open Source is very limited, as it
does not allow customers to initiate the RMA process via web UI, nor does it provide
customers the capability to generate and print shipping labels via web UI. Merchants
lacking critical self-service RMA features, such as those provided by Simple Returns,
make the RMA process more difficult and cumbersome for customers, which can lead to
symptoms like decreased % of repeat customers and increased negative reviews.

#### Customer

+ Create, edit RMA requests from frontend
+ Create, edit, delete RMA file attachments
+ Receive updates via email
+ Generate shipping label upon RMA approval
+ Available to registered customers and guests

#### Administrator

+ Create, edit RMA requests
+ Create RMA packages (return shipments)
+ Generate, print shipping labels
+ Supports several carriers, like Fedex and UPS
+ Customizable RMA fields (e.g. reasons, resolutions, statuses)

#### Developer

+ Dependable and extensible (Simple Returns Pro is living proof!)
+ Plugin-friendly classes and methods
+ Events dispatched for most major actions
+ Actively developed and maintained by [Aurora Extensions](https://partners.magento.com/portal/details/partner/id/2163/), a Magento partner

#### Other features

+ Free<sup>2</sup>, forever and always!

## Installation

```
composer require auroraextensions/simplereturns
```

## Notes

1. Not to be confused with `Magento_Rma`, which is a premium module available with Magento Commerce.
2. Simple Returns is free to everyone, but comes at a cost to us. To help us continue our efforts,
   please consider purchasing Simple Returns Pro, our premium RMA extension that builds on top of
   Simple Returns to provide more advanced features to further optimize your RMA pipeline.
