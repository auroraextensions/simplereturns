Simple Returns
==============

Self-service RMA for Magento.

.. contents::
    :local:

.. |link1| replace:: Overview
.. |link2| replace:: Installation Guide
.. |link3| replace:: Configuration Guide
.. |link4| replace:: Attribute Guide
.. |link5| replace:: List of Events
.. |link6| replace:: Simple Returns Pro
.. _link1: https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/latest/index.html
.. _link2: https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/latest/installation.html
.. _link3: https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/latest/configuration.html
.. _link4: https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/latest/attribute.html
.. _link5: https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/latest/events.html
.. _link6: https://auroraextensions.com/products/simple-returns-pro

Description
-----------

Simple Returns provides enhanced RMA functionality primarily intended for Magento Open Source,
but can also be used with Magento Commerce, if desired. It extends the default RMA capabilities
built into Magento Open Source [#]_, and offers a range of additional features to improve
the RMA experience for both customers and administrators alike.

Getting Started
---------------

Simple Returns is available via Composer.

.. code-block:: sh

   composer require auroraextensions/simplereturns:^1.0

Documentation
-------------

1. |link1|_
2. |link2|_
3. |link3|_
4. |link4|_
5. |link5|_

Features
--------

Simple Returns is packed with features that can substantially improve an RMA pipeline.
The default RMA functionality provided by Magento Open Source is very limited, as it
does not allow customers to initiate the RMA process via web UI, nor does it provide
customers the capability to generate and print shipping labels via web UI. Merchants
lacking critical self-service RMA features, such as those provided by Simple Returns,
make the RMA process more difficult and cumbersome for customers, which can lead to
symptoms like fewer repeat customers and increased negative reviews.

Customer
^^^^^^^^

1. Create, edit RMA requests from frontend
2. Create, edit, delete RMA file attachments
3. Receive updates via email
4. Generate shipping label upon RMA approval
5. Available to registered customers and guests

Administrator
^^^^^^^^^^^^^

1. Create, edit RMA requests
2. Create RMA packages (return shipments)
3. Generate, print shipping labels
4. Supports several carriers, like Fedex and UPS
5. Customizable RMA fields (e.g. reasons, resolutions, statuses)

Developer
^^^^^^^^^

1. Dependable and extensible
2. Plugin-friendly classes and methods
3. Extensive list of events for most major actions
4. Actively developed and maintained since 2019

Other
^^^^^

1. Free [#]_, forever and always!

Notes
-----

.. [#] Not to be confused with `Magento_Rma`, which is a premium module available with Magento Commerce.
.. [#] Simple Returns is free to everyone, but comes at a cost to us. To help us continue our efforts, please
   consider purchasing |link6|_, our premium RMA extension that builds on top of Simple Returns to provide
   more advanced features that can further optimize your RMA pipeline and improve customer experience.
