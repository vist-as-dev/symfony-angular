import {Component, EventEmitter, OnInit, Output} from '@angular/core';
import {Router} from "@angular/router";

import {AuthService} from "@core/security/auth.service";

@Component({
    selector: 'app-full-layout-header',
    templateUrl: './header.component.html',
    styleUrls: ['./header.component.css'],
})
export class FullLayoutHeaderComponent implements OnInit {

    @Output() toggleSideNav = new EventEmitter<void>();

    constructor(private authService: AuthService,
                private router: Router) {
    }

    public logout() {
        this.authService.logout();
        this.router.navigate(['/']);
    }

    ngOnInit(): void {
    }
}
