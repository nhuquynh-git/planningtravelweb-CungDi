import { api } from "./api";

export const getHealth = () => {
    return api.get("/health");
};