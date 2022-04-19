#!/bin/bash

wp theme activate wporg-parent-2021

wp rewrite structure '/%year%/%monthnum%/%postname%/'
wp rewrite flush
