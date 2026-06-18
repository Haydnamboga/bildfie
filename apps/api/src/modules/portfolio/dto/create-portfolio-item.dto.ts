import { IsString, IsNotEmpty, IsOptional, IsEnum, IsUrl, IsDateString } from "class-validator";
import { TradeCategory } from "@prisma/client";

export class CreatePortfolioItemDto {
  @IsString() @IsNotEmpty() title: string;
  @IsOptional() @IsString() description?: string;
  @IsOptional() @IsUrl() imageUrl?: string;
  @IsOptional() @IsEnum(TradeCategory) category?: TradeCategory;
  @IsOptional() @IsDateString() completedAt?: string;
}
