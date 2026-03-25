# Design

[Back](../README.md)

## CRUD

This API is build on the CRUD design. That's **C**reate, **R**ead, **U**pdate, **D**elete.
This is reflected in most of the endpoints.

## Clearance

There are four levels of clearance, each is based on the previous:

1. Guest - Limited readonly access to assets, preview files
2. User - Full readonly access assets, raw files
3. Admin - Full access to all data
4. Overlord - Full access to managing admins

Guest endpoints don't require the Authorization header.
