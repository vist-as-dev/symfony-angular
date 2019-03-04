import {Injectable} from '@angular/core';
import {BehaviorSubject, Observable} from 'rxjs';

import {User} from "@data/model/user.model";

@Injectable({
    providedIn: 'root'
})
export class AuthService {

    private userSubject: BehaviorSubject<User>;
    private user: Observable<User>;

    constructor() {

        this.userSubject = new BehaviorSubject<User>(null);
        this.user = this.userSubject.asObservable();
    }

    public isAuthenticated() {
        return this.getUser();
    }

    public getUser() {

        try {

            return this.userSubject.value;

        } catch (error) {
            return undefined;
        }

    }

    public login(username: string, password: string) {

        this.userSubject.next({username: username, password: password});
        return this.user;
    }

    public logout() {
        this.userSubject.next(null);
    }

}