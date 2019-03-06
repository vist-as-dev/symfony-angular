import {Component, OnInit} from '@angular/core';
import {AuthService} from "@core/security/auth.service";


interface ROUTE {
    icon?: string;
    route?: string;
    title?: string;
}


@Component({
    selector: 'app-full-layout-sidebar',
    templateUrl: './sidebar.component.html',
    styleUrls: ['./sidebar.component.css']
})
export class FullLayoutSidebarComponent implements OnInit {

    myWorkRoutes: ROUTE[] = [
        {
            icon: 'assignment',
            route: 'sales/activities',
            title: 'Activities',
        }, {
            icon: 'dashboard',
            route: 'sales/dashboards',
            title: 'Dashboards',
        }
    ];

    customerRoutes: ROUTE[] = [
        {
            icon: 'contacts',
            route: 'sales/accounts',
            title: 'Accounts',
        }, {
            icon: 'people',
            route: 'sales/contacts',
            title: 'Contacts',
        }, {
            icon: 'settings_phone',
            route: 'sales/leads',
            title: 'Leads',
        }, {
            icon: 'account_box',
            route: 'sales/opportunities',
            title: 'Opportunities',
        }
    ];

    constructor(private authService: AuthService) {
    }

    ngOnInit() {
    }

    public isAuthenticated() {
        return this.authService.isAuthenticated();
    }

}
