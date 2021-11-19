<?php

use Illuminate\Database\Seeder;

class NotificationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insert default permissions
        $settings = [
            ["type"=>"cases",	"sub_type"=>"case",	"topic"=>'A new case is added to the system',	"action"=>"add"],
            ["type"=>"cases",	"sub_type"=>"case",	"topic"=>'An existing case is updated',	"action"=>"update"],
            ["type"=>"cases",	"sub_type"=>"case",	"topic"=>'An open case is closed',	"action"=>'close'],
            ["type"=>"cases",	"sub_type"=>"case",	"topic"=>'A closed case is reopened',	"action"=>'reopened'],
            ["type"=>"cases",	"sub_type"=>"case",	"topic"=>'A closed case is deleted',	"action"=>"delete"],
            ["type"=>"cases",	"sub_type"=>"notes",	"topic"=>'A new note is added, edited, or deleted on a case you\'re linked to notes'],
            ["type"=>"cases",	"sub_type"=>"contact",	"topic"=>'You are added or removed from a case',	"action"=>'link'],
            ["type"=>"cases",	"sub_type"=>"case",	"topic"=>'A contact / company is added or removed from a case you\'re linked to',	"action"=>'contacts'],
            ["type"=>"cases",	"sub_type"=>"case",	"topic"=>'A firm user is added or removed from a case you\'re linked to',	"action"=>'staff'],
            ["type"=>"calendars",	"sub_type"=>"event",	"topic"=>'A new event is added to the system',	"action"=>"add"],
            ["type"=>"calendars",	"sub_type"=>"event",	"topic"=>'An existing event is updated',	"action"=>"update"],
            ["type"=>"calendars",	"sub_type"=>"event",	"topic"=>'Someone deletes an event',	"action"=>"delete"],
            ["type"=>"calendars",	"sub_type"=>"event",	"topic"=>'Someone comments on an event',	"action"=>'comment'],
            ["type"=>"calendars",	"sub_type"=>"event",	"topic"=>'A contact views an event',	"action"=>"view"],
            ["type"=>"documents",	"sub_type"=>"document",	"topic"=>'A new document is uploaded in the system',	"action"=>"add"],
            ["type"=>"documents",	"sub_type"=>"document",	"topic"=>'An existing document is updated',	"action"=>"add"],
            ["type"=>"documents",	"sub_type"=>"document",	"topic"=>'Someone deletes a document',	"action"=>"add"],
            ["type"=>"documents",	"sub_type"=>"document",	"topic"=>'Someone comments on a document',	"action"=>"add"],
            ["type"=>"documents",	"sub_type"=>"document",	"topic"=>'A contact views a document',	"action"=>"add"],
            ["type"=>"tasks",	"sub_type"=>"task",	"topic"=>'A new task is added',	"action"=>"add"],
            ["type"=>"tasks",	"sub_type"=>"task",	"topic"=>'An existing task is updated',	"action"=>"update"],
            ["type"=>"tasks",	"sub_type"=>"task",	"topic"=>'Someone deletes a task',	"action"=>"delete"],
            ["type"=>"tasks",	"sub_type"=>"task",	"topic"=>'A task is completed',	"action"=>'complete'],
            ["type"=>"tasks",	"sub_type"=>"task",	"topic"=>'A completed task is marked incomplete',	"action"=>'incomplete'],
            ["type"=>"billings",	"sub_type"=>"time_entry",	"topic"=>'A new time entry / expense is added',	"action"=>"add"],
            ["type"=>"billings",	"sub_type"=>"time_entry",	"topic"=>'An existing time entry / expense is updated',	"action"=>"update"],
            ["type"=>"billings",	"sub_type"=>"time_entry",	"topic"=>'Someone deletes a time entry / expense',	"action"=>"delete"],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'A new invoice is added to a case you\'re linked to',	"action"=>"add"],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'An existing invoice is updated on a case you\'re linked to',	"action"=>"update"],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'A contact views an invoice',	"action"=>"view"],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'Someone deletes an invoice on a case you\'re linked to',	"action"=>"delete"],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'A payment is made on an invoice on a case you\'re linked to',	"action"=>'payment'],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'A payment is refunded on an invoice on a case you\'re linked to',	"action"=>'refund'],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'Someone shares an invoice on a case you\'re linked to',	"action"=>'share'],
            ["type"=>"billings",	"sub_type"=>"invoices",	"topic"=>'Someone sends a reminder on a case you\'re linked to',	"action"=>'reminder'],
            ["type"=>"contacts",	"sub_type"=>"contact",	"topic"=>'A new contact/company is added to the system',	"action"=>"add"],
            ["type"=>"contacts",	"sub_type"=>"contact",	"topic"=>'An existing contact/company is updated',	"action"=>"update"],
            ["type"=>"contacts",	"sub_type"=>"contact",	"topic"=>'Someone archives a contact/company',	"action"=>'archive'],
            ["type"=>"contacts",	"sub_type"=>"contact",	"topic"=>'Someone unarchives a contact/company',	"action"=>'unarchive'],
            ["type"=>"contacts",	"sub_type"=>"contact",	"topic"=>'Someone deletes a company',	"action"=>"delete"],
            ["type"=>"contacts",	"sub_type"=>"user",	"topic"=>'A contact logs in to LegalCase',	"action"=>'login'],
            ["type"=>"contacts",	"sub_type"=>"contact",	"topic"=>'A new note is added, edited, or deleted on a contact',	"action"=>'notes'],
            ["type"=>"firms",	"sub_type"=>NULL,	"topic"=>'A new firm user is added',	"action"=>"add"],
            ["type"=>"firms",	"sub_type"=>NULL,	"topic"=>'Firm user contact information is updated',	"action"=>"update"],
            ["type"=>"firms",	"sub_type"=>NULL,	"topic"=>'A firm user is deactivated or reactivated',	"action"=>'inactive'],
            ["type"=>"firms",	"sub_type"=>NULL,	"topic"=>'Firm user permissions are changed',	"action"=>"add"],
            ["type"=>"firms",	"sub_type"=>NULL,	"topic"=>'Items are imported into LegalCase',	"action"=>"add"],
            ["type"=>"firms",	"sub_type"=>NULL,	"topic"=>'Firm information is updated',	"action"=>"update"]
        ];
        foreach($settings as $item) {
            NotificationSetting::updateOrCreate(['topic' => $item['topic']], $item);
        }
    }
}
